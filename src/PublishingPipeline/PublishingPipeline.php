<?php


namespace AliSyria\LDOG\PublishingPipeline;


use AliSyria\LDOG\Contracts\PublishingPipeline\PublishingPipelineContract;
use AliSyria\LDOG\Contracts\ShaclValidator\ShaclValidationReportContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\ShapesManager\DataShape;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;
use ML\JsonLD\Document as JsonLdDocument;
use ML\JsonLD\Graph;
use ML\JsonLD\JsonLD;
use ML\JsonLD\Node;
use ML\JsonLD\NQuads;
use ML\JsonLD\TypedValue;

class PublishingPipeline implements PublishingPipelineContract
{
    const CONVERSION_PREFIX=UriBuilder::PREFIX_CONVERSION;
    const PHASES=[
        1=>'RawRdfGeneration',
        2=>'Normalization',
        3=>'Reconciliation',
        4=>'Publishing',
        5=>'LinkToOthersDatasets'
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
    public Node $nodeShape;

    private function __construct(string $id,DataTemplate $dataTemplate,Reader $dataCsvReader,
        JsonLdDocument $configJsonLD,JsonLdDocument $shapeJsonLD,JsonLdDocument $dataJsonLD)
    {
        $this->id=$id;
        $this->dataTemplate=$dataTemplate;
        $this->dataCsv=$dataCsvReader;
        $this->configJsonLD=$configJsonLD;
        $this->shapeJsonLD=$shapeJsonLD;
        $this->dataJsonLD=$dataJsonLD;
        $this->conversionPath=self::getConversionPath($id);
        $this->conversionUri=URI::realResource('meta','Conversion',$id)->getResourceUri();
        $this->nodeShape=$this->shapeJsonLD->getGraph($this->dataTemplate->dataShape->getUri())
            ->getNodesByType(UriBuilder::PREFIX_SHACL.'NodeShape')[0];
        $this->storage=Storage::disk(config('ldog.storage.disk'));
    }

    public static function initiate(DataTemplate $dataTemplate, string $csvPath): self
    {
        $id=Str::uuid();

        $dataCsvReader=self::initiateCsvReader(false,$id,$csvPath);
        $dataJsonLD=self::initiateDatasetJsonLdDocument(false,$id);
        $configJsonLD=self::initiateConfigJsonLdDocument(false,$id,$dataTemplate);
        $shapeJsonLD=self::initiateShapeJsonLdDocument(false,$id,$dataTemplate);

        return new self($id,$dataTemplate,$dataCsvReader,$configJsonLD,$shapeJsonLD,$dataJsonLD);
    }

    public static function make(string $conversionId): self
    {
        $dataTemplate=DataCollectionTemplate::retrieve('http://health.data.example/template/healthFacility');
        $dataCsvReader=self::initiateCsvReader(true,$conversionId);
        $dataJsonLD=self::initiateDatasetJsonLdDocument(true,$conversionId);
        $configJsonLD=self::initiateConfigJsonLdDocument(true,$conversionId);
        $shapeJsonLD=self::initiateShapeJsonLdDocument(true,$conversionId,$dataTemplate);

        return new self($conversionId,$dataTemplate,$dataCsvReader,$configJsonLD,
            $shapeJsonLD,$dataJsonLD);
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
            $conversionNode=$graph->createNode(URI::realResource('meta','Conversion',$conversionId)->getResourceUri());
            $conversionNode->setType(new Node($graph,self::CONVERSION_PREFIX."Conversion"));
            $conversionNode->addPropertyValue(self::CONVERSION_PREFIX."dataTemplate",$dataTemplate->uri);
            foreach (self::PHASES as $phase)
            {
                $phaseNode=$graph->createNode();
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

        $nquads = new NQuads();
        $quads=$nquads->parse(GS::getConnection()->fetchNamedGraph($dataTemplate->dataShape->getUri()));
        if(!$loadFromDisk)
        {
            $disk->put($conversionPath."/shape.jsonld",JsonLD::toString(JsonLD::fromRdf($quads)));
        }
        $shapeJsonLdPath=$disk->path($conversionPath."/shape.jsonld");

        return JsonLD::getDocument($shapeJsonLdPath);
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
        foreach ($this->dataCsv->getRecords() as $record)
        {
            $resource=$this->generateResourceNode($dataGraph,$targetClassName,
                $record[$resourceIdentifierCsvColumnName]);
            $this->attachPredicatesToResource($resource,$record,$mappings,$shapePredicates);
            $this->attachLabelToResourceFromCsv($resource,$record,$mappings,$shapePredicates);
        }
        $this->saveData();
    }

    public function normalize(): void
    {
        // TODO: Implement normalize() method.
    }

    public function reconcile(): void
    {
        // TODO: Implement reconcile() method.
    }

    public function validate(): ShaclValidationReportContract
    {
        // TODO: Implement validate() method.
    }

    public function publish(): void
    {
        // TODO: Implement publish() method.
    }

    public function linkToOthersDatasets(): void
    {
        // TODO: Implement linkToOthersDatasets() method.
    }

    private function mapCsvColumnNamesToShapeProperties(): void
    {

    }

    private function updateObjectValue(string $predicate,$oldTerm,$newTerm):void
    {

    }

    private function bulkUpdateObjectValues(string $predicate,$oldTerm,$newTerm):void
    {

    }

    public function attachPredicatesToResource(Node $resource,array $record,array $mappings,Collection $shapePredicates)
    {
        foreach ($mappings as $predicateUri=>$columnName)
        {
            $dataTypeUri=$shapePredicates->where('uri',$predicateUri)->first()->dataType;
            $resource->addPropertyValue($predicateUri,new TypedValue($record[$columnName],$dataTypeUri));
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

        return $graph->createNode($uri);
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
            $columnPredicateMappingNode=$graph->createNode();
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
    public function getShapePredicates():Collection
    {
        $propeties=$this->nodeShape->getProperty(UriBuilder::PREFIX_SHACL."property");

        $predicates=[];
        foreach ($propeties as $property)
        {
            $predicates[]=new Predicate(
                $property->getProperty(UriBuilder::PREFIX_SHACL.'path')->getId(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'name')->getValue(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'description')->getValue(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'order')->getValue(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'datatype')->getId(),
                optional($property->getProperty(UriBuilder::PREFIX_SHACL.'class'))->getId(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'minCount')->getValue(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'maxCount')->getValue(),
                $property->getProperty(UriBuilder::PREFIX_SHACL.'message')->getValue(),
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
    public function getCsvColumnNames():array
    {
        return $this->dataCsv->getHeader();
    }

    private function saveConfig()
    {
        $this->storage->put($this->conversionPath."/config.jsonld",JsonLD::toString($this->configJsonLD->toJsonLd()));
    }
    private function saveData()
    {
        $this->storage->put($this->conversionPath."/dataset.jsonld",JsonLD::toString($this->dataJsonLD->toJsonLd()));
    }
}
//        $properties=$shapeJsonLD->getGraph($dataTemplate->dataShape->getUri())->getNode('http://health.data.ae/shape/health-facility-spape#HealthFacilityShape')
//            ->getProperty("http://www.w3.org/ns/shacl#property");
//        dd($properties[0]->getProperties()['http://www.w3.org/ns/shacl#name']);
//$storage->put($conversionPath."/mapping.sparql","");
//$mappingSparqlPath=$storage->path($conversionPath."/mapping.sparql");