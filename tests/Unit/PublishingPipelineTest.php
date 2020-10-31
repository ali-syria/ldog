<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\PublishingPipeline\PublishingPipeline;
use AliSyria\LDOG\ShapesManager\ShapeManager;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use Illuminate\Support\Facades\Storage;

class PublishingPipelineTest extends TestCase
{
    protected string $shapeUrl="http://api.eresta.test/shapes/HealthFacility.ttl";

    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        GS::getConnection()
            ->loadIRIintoNamedGraph('http://api.eresta.test/ontology/ldog.ttl',
                'http://ldog.com/ontology');
    }

    public function testInitiatePipeline()
    {
        Storage::fake(config('ldog.storage.disk'));
        $disk=Storage::disk(config('ldog.storage.disk'));
        $conversionsDirectory=config('ldog.storage.directories.root')."/".config('ldog.storage.directories.conversions')."/";
        $csvPath=__DIR__."/../Datasets/PublishingExamples/Facilities/Sheryan_Facility_Detail.csv";

        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $dataDomain=DataDomain::find($ldogPrefix.DataDomain::HEALTH);
        $dataExportTarget=DataExporterTarget::find($ldogPrefix.DataExporterTarget::MODELLING_ORGANIZATION);

        $identifier='HealthFacility';
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $dataShape=ShapeManager::importFromUrl($this->shapeUrl,$dataDomain->subDomain,$identifier);

        $dataCollectionTemplate=DataCollectionTemplate::create(
            $identifier,'Health Facilities Template','Health Facilities information in each emirate',
            $dataShape,$cabinet,$dataExportTarget,$dataDomain
        );

        $pipeline=PublishingPipeline::initiate($dataCollectionTemplate,$csvPath);

        $this->assertInstanceOf(PublishingPipeline::class,$pipeline);
        $disk->assertExists($conversionsDirectory.$pipeline->id.'/dataset.csv');
        $disk->assertExists($conversionsDirectory.$pipeline->id.'/dataset.jsonld');
        $disk->assertExists($conversionsDirectory.$pipeline->id.'/config.jsonld');
        $disk->assertExists($conversionsDirectory.$pipeline->id.'/shape.jsonld');
        $disk->assertExists($conversionsDirectory.$pipeline->id.'/mapping.sparql');
    }
}