<?php


namespace AliSyria\LDOG\PublishingPipeline;


use AliSyria\LDOG\BatchImporter\DataCollection;
use AliSyria\LDOG\BatchImporter\Report;
use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\PublishingPipeline\PublishingPipelineContract;
use AliSyria\LDOG\Contracts\ShaclValidator\ShaclValidationReportContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\Facades\VAL;
use AliSyria\LDOG\Jobs\LinkToOthersDatasetsJob;
use AliSyria\LDOG\Normalization\Normalizer;
use AliSyria\LDOG\OuterLinkage\SilkOutLinker;
use AliSyria\LDOG\ShapesManager\DataShape;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\TemplateBuilder\ReportTemplate;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;
use ML\JsonLD\Document as JsonLdDocument;
use ML\JsonLD\Graph;
use ML\JsonLD\JsonLD;
use ML\JsonLD\Node;
use ML\JsonLD\NQuads;
use ML\JsonLD\TypedValue;
use PHPUnit\Util\Type;

class PublishingPipeline implements PublishingPipelineContract
{
    const CONVERSION_PREFIX=UriBuilder::PREFIX_CONVERSION;
    const PHASES=[
        1=>'RawRdfGeneration',
        2=>'Normalization',
        3=>'Reconciliation',
        4=>'Validation',
        5=>'Publishing',
        6=>'LinkToOthersDatasets'
    ];

    public string $id;
    public string $conversionUri;
    public DataTemplate $dataTemplate;
    public Reader $dataCsv;
    public JsonLdDocument $configJsonLD;
    public JsonLdDocument $shapeJsonLD;
    public JsonLdDocument $dataJsonLD;
    public FilesystemAdapter $storage;
    public string $conversionPath;
    public ?string $silkLslSpecsPath;
    public Node $nodeShape;

    private function __construct(string $id,DataTemplate $dataTemplate,Reader $dataCsvReader,
        JsonLdDocument $configJsonLD,JsonLdDocument $shapeJsonLD,JsonLdDocument $dataJsonLD,
        string $silkLslSpecsPath=null)
    {
        $this->id=$id;
        $this->dataTemplate=$dataTemplate;
        $this->dataCsv=$dataCsvReader;
        $this->configJsonLD=$configJsonLD;
        $this->shapeJsonLD=$shapeJsonLD;
        $this->dataJsonLD=$dataJsonLD;
        $this->conversionPath=self::getConversionPath($id);
        $this->conversionUri=URI::realResource('meta','Conversion',$id)->getResourceUri();
        $this->nodeShape=$this->shapeJsonLD->getGraph()
            ->getNodesByType(UriBuilder::PREFIX_SHACL.'NodeShape')[0];
        $this->storage=Storage::disk(config('ldog.storage.disk'));
        $this->silkLslSpecsPath=$silkLslSpecsPath;
    }

    public static function initiate(DataTemplate $dataTemplate, string $csvPath): self
    {
        $id=Str::uuid();

        $dataCsvReader=self::initiateCsvReader(false,$id,$csvPath);
        $dataJsonLD=self::initiateDatasetJsonLdDocument(false,$id);
        $configJsonLD=self::initiateConfigJsonLdDocument(false,$id,$dataTemplate);
        $shapeJsonLD=self::initiateShapeJsonLdDocument(false,$id,$dataTemplate);
        $silkLslSpecsPath=self::initiateSilkLslSpecs(false,$id,$dataTemplate);

        return new self($id,$dataTemplate,$dataCsvReader,$configJsonLD,$shapeJsonLD,$dataJsonLD,
            $silkLslSpecsPath);
    }

    public static function make(string $conversionId): self
    {
        $dataCsvReader=self::initiateCsvReader(true,$conversionId);
        $dataJsonLD=self::initiateDatasetJsonLdDocument(true,$conversionId);
        $configJsonLD=self::initiateConfigJsonLdDocument(true,$conversionId);
        $dataTemplateUri=$configJsonLD->getGraph()->getNode(self::getConversionUri($conversionId))
            ->getProperty(self::CONVERSION_PREFIX."dataTemplate")
            ->getValue();
        $dataTemplate=DataCollectionTemplate::retrieve($dataTemplateUri);
        if(is_null($dataTemplate))
        {
            $dataTemplate=ReportTemplate::retrieve($dataTemplateUri);
        }
        $shapeJsonLD=self::initiateShapeJsonLdDocument(true,$conversionId,$dataTemplate);
        $silkLslSpecsPath=self::initiateSilkLslSpecs(true,$conversionId,$dataTemplate);

        return new self($conversionId,$dataTemplate,$dataCsvReader,$configJsonLD,
            $shapeJsonLD,$dataJsonLD,$silkLslSpecsPath);
    }

    public static function getDisk():FilesystemAdapter
    {
        return Storage::disk(config('ldog.storage.disk'));
    }
    public static function getConversionPath(string $id):string
    {
        return config('ldog.storage.directories.root')."/".
            config('ldog.storage.directories.conversions')."/".$id;
    }
    public static function initiateCsvReader(bool $loadFromDisk,string $conversionId,
       string $csvPath=null):Reader
    {
        $disk=self::getDisk();
        $conversionPath=self::getConversionPath($conversionId);
        if(!$loadFromDisk)
        {
            $disk->putFileAs($conversionPath,new File($csvPath),'dataset.csv');
        }
        $dataCsvPath=$disk->path($conversionPath."/dataset.csv");
        $dataCsvReader=Reader::createFromPath($dataCsvPath);
        $dataCsvReader->setHeaderOffset(0);

        return $dataCsvReader;
    }
    public static function initiateDatasetJsonLdDocument(bool $loadFromDisk,string $conversionId):JsonLdDocument
    {
        $disk=self::getDisk();
        $conversionPath=self::getConversionPath($conversionId);

        if(!$loadFromDisk)
        {
            $disk->put($conversionPath."/dataset.jsonld","{}");
        }
        $dataJsonLdPath=$disk->path($conversionPath."/dataset.jsonld");

        return JsonLD::getDocument($dataJsonLdPath);
    }
    public static function initiateConfigJsonLdDocument(bool $loadFromDisk,string $conversionId,DataTemplate $dataTemplate=null):JsonLdDocument
    {
        $disk=self::getDisk();
        $conversionPath=self::getConversionPath($conversionId);

       if(!$loadFromDisk)
       {
           $disk->put($conversionPath."/config.jsonld","{}");
       }
       $configJsonLdPath=$disk->path($conversionPath."/config.jsonld");
       $configJsonLd=JsonLD::getDocument($configJsonLdPath);
        if(!$loadFromDisk)
        {
            if(is_null($dataTemplate))
            {
                throw new \RuntimeException('dataTemplate is required to initiate config');
            }
            $graph=$configJsonLd->getGraph();
            $conversionNode=$graph->createNode(self::getConversionUri($conversionId));
            $conversionNode->setType(new Node($graph,self::CONVERSION_PREFIX."Conversion"));
            $conversionNode->addPropertyValue(self::CONVERSION_PREFIX."dataTemplate",$dataTemplate->uri);
            foreach (self::PHASES as $phase)
            {
                $phaseNode=$graph->createNode(self::getConversionUri($conversionId)."/phase/$phase");
                $phaseNode->setType(new Node($graph,self::CONVERSION_PREFIX.$phase));
                $conversionNode->addPropertyValue(self::CONVERSION_PREFIX."hasConversionPhase",$phaseNode);
            }
            $disk->put($conversionPath."/config.jsonld",JsonLD::toString($configJsonLd->toJsonLd()));
        }
        return $configJsonLd;
    }
    public static function initiateShapeJsonLdDocument(bool $loadFromDisk,string $conversionId,DataTemplate $dataTemplate):JsonLdDocument
    {
        $disk=self::getDisk();
        $conversionPath=self::getConversionPath($conversionId);

        if(!$loadFromDisk)
        {
            $nquads = new NQuads();
            $quads=$nquads->parse(GS::getConnection()->fetchNamedGraph($dataTemplate->dataShape->getUri()));
            $disk->put($conversionPath."/shape.jsonld",JsonLD::toString(JsonLD::fromRdf($quads)));
            $shapeJsonLdPath=$disk->path($conversionPath."/shape.jsonld");
            $jsonLdDocument=JsonLD::getDocument($shapeJsonLdPath);
            $jsonLdDocument->getGraph()->merge($jsonLdDocument->getGraph($dataTemplate->dataShape->getUri()));
            $jsonLdDocument->removeGraph($dataTemplate->dataShape->getUri());
            $disk->put($conversionPath."/shape.jsonld",JsonLD::toString($jsonLdDocument->toJsonLd()));
        }
        $shapeJsonLdPath=$disk->path($conversionPath."/shape.jsonld");

        return JsonLD::getDocument($shapeJsonLdPath);
    }
    public static function initiateSilkLslSpecs(bool $loadFromDisk,string $conversionId,DataTemplate $dataTemplate):?string
    {
        if(is_null($dataTemplate->silkLslSpecs))
        {
            return null;
        }
        $disk=self::getDisk();
        $conversionPath=self::getConversionPath($conversionId);

        if(!$loadFromDisk)
        {
            $disk->put($conversionPath."/silk-linkage-specs.xml",$dataTemplate->silkLslSpecs);
        }
        $silkLinkageSpecsPath=$conversionPath."/silk-linkage-specs.xml";

        return $silkLinkageSpecsPath;
    }


    public function generateRawRdf(array $mappings): void
    {
        $this->mapColumnsToPredicates($mappings);
        $targetClassUri=$this->getTargetClassUri();
        $targetClassName=$this->getTargetClassName();
        $resourceIdentifierPropertyUri=$this->getResourceIdentifierPropertyUri();
        $resourceIdentifierCsvColumnName=$mappings[$resourceIdentifierPropertyUri];

        $dataGraph=$this->dataJsonLD->getGraph();
        $shapePredicates=$this->getShapePredicates();
        foreach ($this->dataCsv->skipEmptyRecords()->getRecords() as $record)
        {
            if(blank($record[$resourceIdentifierCsvColumnName]))
            {
                continue;
            }
            $resource=$this->generateResourceNode($dataGraph,$targetClassName,
                $record[$resourceIdentifierCsvColumnName]);
            $this->attachPredicatesToResource($resource,$record,$mappings,$shapePredicates);
            $this->attachLabelToResourceFromCsv($resource,$record,$mappings,$shapePredicates);
        }
        $this->storage->put($this->conversionPath."/dataset.jsonld",JsonLD::toString($this->dataJsonLD->toJsonLd()));
        //$this->saveData();
    }

    public function normalize():void
    {
        $dataJsonLd=self::initiateDatasetJsonLdDocument(true,$this->id);
        $graph=$dataJsonLd->getGraph();
        $resourceNodes=$graph->getNodesByType($this->getTargetClassUri());

        foreach ($resourceNodes as $resourceNode)
        {
            foreach ($resourceNode->getProperties() as $predicate=>$object)
            {
                if(!($object instanceof TypedValue))
                {
                    continue;
                }
                $shapePredicate=$this->getShapePredicates()->where('uri',$predicate)->first();
                if(is_null(optional($shapePredicate)->normalizedByFunction))
                {
                    continue;
                }
                $resourceNode->removeProperty($predicate);
                $resourceNode->addPropertyValue($predicate,Normalizer::handle($object->getValue(),$shapePredicate->normalizedByFunction));
            }
        }


        $this->storage->put($this->conversionPath."/dataset.jsonld",JsonLD::toString($dataJsonLd->toJsonLd()));
    }

    public function reconcile(Collection $termResourceMappings): void
    {
        $shapeObjectPredicates=$this->getShapeObjectPredicates();
        $this->mapTermsToResources($termResourceMappings);
        $dataJsonLd=self::initiateDatasetJsonLdDocument(true,$this->id);
        $graph=$dataJsonLd->getGraph();
        $resourceNodes=$graph->getNodesByType($this->getTargetClassUri());

        foreach ($resourceNodes as $resourceNode)
        {
            $resourceProperties=$resourceNode->getProperties();
            foreach ($shapeObjectPredicates as $shapeObjectPredicate)
            {
                $object=$resourceProperties[$shapeObjectPredicate->uri] ?? null;
                if(!is_null($object)&&!($object instanceof TypedValue))
                {
                    continue;
                }
                foreach ($termResourceMappings->where('predicate',$shapeObjectPredicate->uri) as $termResourceMapping)
                {
                    if(optional($object)->getValue()==$termResourceMapping->term)
                    {
                        $resourceNode->removeProperty($shapeObjectPredicate->uri);
                        $targetNode=$graph->createNode($termResourceMapping->resource);
                        $targetNode->setType(new Node($graph,$shapeObjectPredicates->where('uri',$termResourceMapping->predicate)->first()->objectClassUri));
                        $resourceNode->addPropertyValue($shapeObjectPredicate->uri,$targetNode);
                    }
                }
            }
        }

        $this->storage->put($this->conversionPath."/dataset.jsonld",JsonLD::toString($dataJsonLd->toJsonLd()));
    }

    public function validate(): ShaclValidationReportContract
    {
        return VAL::validateGraph($this->storage->path($this->conversionPath."/dataset.jsonld"),
            $this->storage->path($this->conversionPath."/shape.jsonld"));
    }

    public function publish(OrganizationContract $organization,EmployeeContract $employee,
                            Carbon $fromDate=null,Carbon $toDate=null): void
    {
        $conversionPath=self::getConversionPath($this->id);

        if($this->dataTemplate instanceof DataCollectionTemplate)
        {
            DataCollection::create($this->id,$this->storage->path($conversionPath."/config.jsonld"),
                $this->storage->path($conversionPath."/dataset.jsonld")," ",null,$this->dataTemplate,
                $organization,$employee,$fromDate,$toDate);
        }
        elseif ($this->dataTemplate instanceof ReportTemplate)
        {
            Report::create($this->id,$this->storage->path($conversionPath."/config.jsonld"),
                $this->storage->path($conversionPath."/dataset.jsonld")," ",null,$this->dataTemplate,
                $organization,$employee,$fromDate,$toDate);
        }
        else
        {
            throw new \RuntimeException('Invalid Template');
        }
    }

    public function linkToOthersDatasets(): void
    {
        if(blank($this->silkLslSpecsPath))
        {
            return;
        }
        LinkToOthersDatasetsJob::dispatch(config('ldog.storage.disk'),$this->silkLslSpecsPath)
            ->onConnection(config('ldog.silk.queue_connection'))
            ->onQueue(config('ldog.silk.queue_name'));
    }

    public function updateIndex():void
    {
        Artisan::queue('ldog:init-graphdb-lucene');
    }

    public function updateObjectValue(Node $resource,string $predicateUri,$oldTerm,$newTerm,bool $save=true):void
    {
        $shapePredicates=$this->getShapePredicates();
        $shapePredicate=$shapePredicates->where('uri',$predicateUri)->first();
        $resource->removeProperty($predicateUri);

        $newTerm=$this->sanitize($newTerm);
        if(blank($newTerm))
        {

        }
        elseif(!$shapePredicate->isObjectPredicate())
        {
            $resource->addPropertyValue($predicateUri,new TypedValue($newTerm,$shapePredicate->dataType));
        }
        else
        {
            $graph=$resource->getGraph();
            $objectNode=$graph->createNode($newTerm);
            $objectNode->setType(new Node($graph,$shapePredicate->objectClassUri));
            $resource->addPropertyValue($predicateUri,$objectNode);
        }

        if($save)
        {
            $this->saveData();
        }
        $this->mapTermsToReplacements(false,$predicateUri,$oldTerm,$newTerm,$resource->getId());
    }

    public function bulkUpdateObjectValues(string $predicateUri,$oldTerm,$newTerm):void
    {
        $graph=$this->dataJsonLD->getGraph();
        foreach ($this->getObjectOccurences($predicateUri,$oldTerm) as $resource)
        {
            $this->updateObjectValue($graph->getNode($resource->getId()),$predicateUri,$oldTerm,$newTerm,false);
        }

        $this->saveData();
        $this->mapTermsToReplacements(true,$predicateUri,$oldTerm,$newTerm);
    }

    public static function getConversionUri(string $conversionId):string
    {
        return URI::realResource('meta','Conversion',$conversionId)->getResourceUri();
    }
    public function attachPredicatesToResource(Node $resource,array $record,array $mappings,Collection $shapePredicates)
    {
        foreach ($mappings as $predicateUri=>$columnName)
        {
            $shapePredicate=$shapePredicates->where('uri',$predicateUri)->first();
            $columnValue=$this->sanitize($record[$columnName]);
            if(blank($columnValue))
            {
                continue;
            }

            if(!$shapePredicate->isObjectPredicate())
            {
                $resource->addPropertyValue($predicateUri,new TypedValue($columnValue,$shapePredicate->dataType));
                continue;
            }
            else
            {
                $resource->addPropertyValue($predicateUri,$columnValue);
            }
        }
    }
    public function attachLabelToResourceFromCsv(Node $resource,array $record,array $mappings,Collection $shapePredicates)
    {
        $labelExpression=Str::of($this->getResourceLabelExpression());
        foreach ($shapePredicates as $shapePredicate)
        {
            $placeholder="{".$shapePredicate->name."}";
            if($labelExpression->contains($placeholder))
            {
                $textToReplace=$record[$mappings[$shapePredicate->uri]];
                $labelExpression=$labelExpression->replace($placeholder,$textToReplace);
            }
        }
        $resource->addPropertyValue(UriBuilder::PREFIX_RDFS.'label',(string)$labelExpression);
    }
    public function generateResourceNode(Graph $graph,string $targetClassName,string $identifier):Node
    {
        $uri=URI::realResource($this->dataTemplate->dataDomain->subDomain,$targetClassName,$identifier)
            ->getResourceUri();
        $node=$graph->createNode($uri);
        $node->setType(new Node($graph,$this->getTargetClassUri()));

        return $node;
    }
    public function getTargetClassUri():string
    {
        return $this->nodeShape->getProperty(UriBuilder::PREFIX_SHACL.'targetClass')->getId();
    }
    public function getTargetClassName():string
    {
        $targetClassUri=$this->getTargetClassUri();
        return self::extractClassNameFromUri($targetClassUri);
    }
    public function getResourceIdentifierPropertyUri():string
    {
        return $this->nodeShape->getProperty(UriBuilder::PREFIX_LDOG."resourceIdentifierProperty")
            ->getId();
    }
    public function getResourceLabelExpression():string
    {
        return $this->nodeShape->getProperty(UriBuilder::PREFIX_LDOG."resourceLabelExpression")
            ->getValue();
    }
    public static function extractClassNameFromUri(string $classUri):string
    {
        if(Str::contains($classUri,'#'))
        {
            return Str::after($classUri,'#');
        }
        else
        {
            return Str::afterLast($classUri,'/');
        }
    }
    public function mapColumnsToPredicates(array $mappings):void
    {
        $graph=$this->configJsonLD->getGraph();
        $rawRdfGenerationNode=$graph->getNodesByType(self::CONVERSION_PREFIX.'RawRdfGeneration')[0];

        foreach ($mappings as $predicateUri=>$columnName)
        {
            $columnPredicateMappingNode=$graph->createNode(self::getConversionUri($this->id)."/column-predicate-mapping/".Str::uuid());
            $columnPredicateMappingNode->setType(new Node($graph,self::CONVERSION_PREFIX."ColumnPredicateMapping"));
            $columnPredicateMappingNode->addPropertyValue(self::CONVERSION_PREFIX."columnName",$columnName);
            $predicate=$graph->createNode($predicateUri);
            $columnPredicateMappingNode->addPropertyValue(self::CONVERSION_PREFIX."predicate",$predicate);
            $predicateLabel=$this->getShapePredicates()->where('uri',$predicateUri)->first()->name;
            $columnPredicateMappingNode->addPropertyValue(self::CONVERSION_PREFIX."predicateLabel",$predicateLabel);
            $rawRdfGenerationNode->addPropertyValue(self::CONVERSION_PREFIX.'hasColumnPredicateMapping',$columnPredicateMappingNode);
        }
        $this->saveConfig();
    }
    public function mapTermsToResources(Collection $termResourceMappings)
    {
        $graph=$this->configJsonLD->getGraph();
        $reconciliationNode=$graph->getNodesByType(self::CONVERSION_PREFIX.'Reconciliation')[0];

        foreach ($termResourceMappings as $termResourceMapping)
        {
            $termResourceMappingNode=$graph->createNode(self::getConversionUri($this->id)."/term-resource-mapping/".Str::uuid());
            $termResourceMappingNode->setType(new Node($graph,self::CONVERSION_PREFIX."TermResourceMapping"));
            $predicate=$graph->createNode($termResourceMapping->predicate);
            $termResourceMappingNode->addPropertyValue(self::CONVERSION_PREFIX."predicate",$predicate);
            $termResourceMappingNode->addPropertyValue(self::CONVERSION_PREFIX."term",$termResourceMapping->term);
            $resource=$graph->createNode($termResourceMapping->resource);
            $termResourceMappingNode->addPropertyValue(self::CONVERSION_PREFIX."resource",$resource);
            $matchType=$graph->createNode($termResourceMapping->matchType);
            $termResourceMappingNode->addPropertyValue(self::CONVERSION_PREFIX.'matchType',$matchType);
            $reconciliationNode->addPropertyValue(self::CONVERSION_PREFIX.'hasTermResourceMapping',$termResourceMappingNode);
        }
        $this->saveConfig();
    }
    public function mapTermsToReplacements(bool $isBulkReplacement,$predicate,$term,$replacement,$resource=null)
    {
        if(!$isBulkReplacement && is_null($resource))
        {
            throw new \RuntimeException('resource required in case of a single replacement');
        }
        $class='SingleObjectReplacement';
        if($isBulkReplacement)
        {
            $class='BulkObjectReplacement';
        }

        $graph=$this->configJsonLD->getGraph();
        $validationNode=$graph->getNodesByType(self::CONVERSION_PREFIX.'Validation')[0];

        $objectReplacementNode=$graph->createNode(self::getConversionUri($this->id)."/".Str::kebab($class)."/".Str::uuid());
        $objectReplacementNode->setType(new Node($graph,self::CONVERSION_PREFIX.$class));
        $predicate=$graph->createNode($predicate);
        $objectReplacementNode->addPropertyValue(self::CONVERSION_PREFIX."predicate",$predicate);
        $objectReplacementNode->addPropertyValue(self::CONVERSION_PREFIX."term",$term);
        $objectReplacementNode->addPropertyValue(self::CONVERSION_PREFIX."replacedBy",$replacement);
        if(!$isBulkReplacement)
        {
            $resource=$graph->createNode($resource);
            $objectReplacementNode->addPropertyValue(self::CONVERSION_PREFIX."resource",$resource);
        }

        $validationNode->addPropertyValue(self::CONVERSION_PREFIX.'hasObjectReplacement',$objectReplacementNode);

        $this->saveConfig();
    }
    public function getShapePredicates():Collection
    {
        $propeties=$this->nodeShape->getProperty(UriBuilder::PREFIX_SHACL."property");
        if(!is_array($propeties))
        {
            $propeties=[$propeties];
        }
        $predicates=[];
        foreach ($propeties as $property)
        {
            $path=$property->getProperty(UriBuilder::PREFIX_SHACL.'path')->getId();
            if(in_array($path,[UriBuilder::PREFIX_RDF.'type',UriBuilder::PREFIX_RDFS.'label']))
            {
                continue;
            }
            $predicates[]=new Predicate(
                $path,
                $property->getProperty(UriBuilder::PREFIX_SHACL.'name')->getValue(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'description')->getValue(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'order')->getValue(),
                optional($property->getProperty(UriBuilder::PREFIX_SHACL.'datatype'))->getId(),
                optional($property->getProperty(UriBuilder::PREFIX_SHACL.'class'))->getId(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'minCount')->getValue(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'maxCount')->getValue(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'message')->getValue(),
                optional($property->getProperty(UriBuilder::PREFIX_LDOG.'normalizedBy'))->getId(),
            );
        }

        return collect($predicates);
    }
    public function getShapeObjectPredicates():Collection
    {
        return $this->getShapePredicates()->whereNotNull('objectClassUri');
    }
    public function getShapeDataPredicates():Collection
    {
        return $this->getShapePredicates()->whereNull('objectClassUri');
    }
    public function getShapeObjectPredicateClassResources(Predicate $predicate):Collection
    {
        if(!$predicate->isObjectPredicate())
        {
            return collect([]);
        }
        return GS::getConnection()->getClassResourceLabels($predicate->objectClassUri);
    }
    public function getCsvColumnNames():array
    {
        return $this->dataCsv->getHeader();
    }
    public function getCsvColumnIndex(string $column):int
    {
        return array_flip($this->dataCsv->getHeader())[$column];
    }
    public function getCsvDistinctColumnValues(string $column):array
    {
        $columnIndex=$this->getCsvColumnIndex($column);
        return array_values(array_unique(iterator_to_array($this->dataCsv->skipEmptyRecords()->fetchColumn($columnIndex),false)));
    }
    public function getCsvColumnNameForPredicate(Predicate $predicate):string
    {
        $graph=$this->configJsonLD->getGraph();
        $columnPredicateMappings=$graph->getNodesByType(self::CONVERSION_PREFIX.'ColumnPredicateMapping');
        $columnName=null;
        foreach ($columnPredicateMappings as $columnPredicateMapping)
        {
            if($columnPredicateMapping->getProperty(self::CONVERSION_PREFIX."predicate")->getId()==$predicate->uri)
            {
                $columnName= $columnPredicateMapping->getProperty(self::CONVERSION_PREFIX."columnName")->getValue();
                break;
            }
        }
        return  $columnName;
    }
    private function saveConfig()
    {
        $this->storage->put($this->conversionPath."/config.jsonld",JsonLD::toString($this->configJsonLD->toJsonLd()));
    }
    private function saveData()
    {
        $this->storage->put($this->conversionPath."/dataset.jsonld",JsonLD::toString($this->dataJsonLD->toJsonLd()));
    }
    public function getResourceNodes()
    {
        $dataJsonLd=self::initiateDatasetJsonLdDocument(true,$this->id);
        $graph=$dataJsonLd->getGraph();
        return $graph->getNodesByType($this->getTargetClassUri());
    }
    public function getResourceNode(string $uri)
    {
        $graph=$this->dataJsonLD->getGraph();
        return $graph->getNode($uri);
    }
    public function getObjectOccurences(string $predicateUri,?string $value):Collection
    {
        return collect($this->getResourceNodes())->filter(function($resource,$key)use($predicateUri,$value){
            $object=collect($resource->getProperties())
                ->filter(function($property,$key)use($predicateUri){
                    return $key==$predicateUri;
                })->first();

            if(is_null($object))
            {
                return false;
            }
            if($object instanceof TypedValue)
            {
                return Str::lower($object->getValue())==Str::lower($value);
            }
            elseif($object instanceof Node)
            {
                return $object->getId()==$value;
            }
            else
            {
                return false;
            }
        });
    }
    public function getObjectOccurencesCount(string $predicateUri,?string $value):int
    {
        return $this->getObjectOccurences($predicateUri,$value)->count();
    }
    private function convertEmptyStringsToNull(string $value):?string
    {
        return is_string($value) && $value === '' ? null : $value;
    }
    private function sanitize(?string $value):?string
    {
        if(is_null($value))
        {
            return null;
        }
        $value=$this->convertEmptyStringsToNull($value);
        if(is_null($value))
        {
            return null;
        }
        $value=(string)Str::of($value)->trim();
        if(is_null($value))
        {
            return null;
        }

        return $value;
    }
}
//        $properties=$shapeJsonLD->getGraph($dataTemplate->dataShape->getUri())->getNode('http://health.data.ae/shape/health-facility-spape#HealthFacilityShape')
//            ->getProperty("http://www.w3.org/ns/shacl#property");
//        dd($properties[0]->getProperties()['http://www.w3.org/ns/shacl#name']);
//$storage->put($conversionPath."/mapping.sparql","");
//$mappingSparqlPath=$storage->path($conversionPath."/mapping.sparql");