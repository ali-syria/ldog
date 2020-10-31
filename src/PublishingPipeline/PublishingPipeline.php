<?php


namespace AliSyria\LDOG\PublishingPipeline;


use AliSyria\LDOG\Contracts\PublishingPipeline\PublishingPipelineContract;
use AliSyria\LDOG\Contracts\ShaclValidator\ShaclValidationReportContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\ShapesManager\DataShape;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;
use ML\JsonLD\Document as JsonLdDocument;
use ML\JsonLD\JsonLD;
use ML\JsonLD\NQuads;

class PublishingPipeline implements PublishingPipelineContract
{
    public string $id;
    public Reader $dataCsv;
    public JsonLdDocument $configJsonLD;
    public JsonLdDocument $shapeJsonLD;
    public JsonLdDocument $dataJsonLD;

    public DataTemplate $dataTemplate;
    public DataShape $dataShape;
    private Filesystem $storage;

    public function __construct(string $id,Reader $dataCsvReader,JsonLdDocument $configJsonLD,
                                JsonLdDocument $shapeJsonLD,JsonLdDocument $dataJsonLD)
    {
        $this->id=$id;
        $this->dataCsv=$dataCsvReader;
        $this->configJsonLD=$configJsonLD;
        $this->shapeJsonLD=$shapeJsonLD;
        $this->dataJsonLD=$dataJsonLD;
    }

    public static function initiate(DataTemplate $dataTemplate, string $csvPath): PublishingPipelineContract
    {
        $id=Str::uuid();
        $storage=Storage::disk(config('ldog.storage.disk'));
        $conversionPath=config('ldog.storage.directories.root')."/".
            config('ldog.storage.directories.conversions')."/".$id;
        $storage->putFileAs($conversionPath,new File($csvPath),'dataset.csv');
        $dataCsvPath=$storage->path($conversionPath."/dataset.csv");
        $dataCsvReader=Reader::createFromPath($dataCsvPath);
        $dataCsvReader->setHeaderOffset(0);

        $storage->put($conversionPath."/dataset.jsonld","{}");
        $dataJsonLdPath=$storage->path($conversionPath."/dataset.jsonld");
        $dataJsonLD=JsonLD::getDocument($dataJsonLdPath);

        $storage->put($conversionPath."/config.jsonld","{}");
        $configJsonLdPath=$storage->path($conversionPath."/config.jsonld");
        $configJsonLD=JsonLD::getDocument($configJsonLdPath);


        $nquads = new NQuads();
        $quads=$nquads->parse(GS::getConnection()->fetchNamedGraph($dataTemplate->dataShape->getUri()));
        $storage->put($conversionPath."/shape.jsonld",JsonLD::toString(JsonLD::fromRdf($quads)));
        $shapeJsonLdPath=$storage->path($conversionPath."/shape.jsonld");
        $shapeJsonLD=JsonLD::getDocument($shapeJsonLdPath);
        $storage->put($conversionPath."/mapping.sparql","");
        $mappingSparqlPath=$storage->path($conversionPath."/mapping.sparql");

        return new self($id,$dataCsvReader,$configJsonLD,$shapeJsonLD,$dataJsonLD);
//        $properties=$shapeJsonLD->getGraph($dataTemplate->dataShape->getUri())->getNode('http://health.data.ae/shape/health-facility-spape#HealthFacilityShape')
//            ->getProperty("http://www.w3.org/ns/shacl#property");
//        dd($properties[0]->getProperties()['http://www.w3.org/ns/shacl#name']);
    }

    public static function make(string $conversionUuid): PublishingPipelineContract
    {
        // TODO: Implement make() method.
    }

    public function generateRawRdf(): void
    {
        // TODO: Implement generateRawRdf() method.
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
}