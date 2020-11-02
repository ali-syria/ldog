<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
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
    protected DataCollectionTemplate $dataCollectionTemplate;

    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        GS::getConnection()
            ->loadIRIintoNamedGraph('http://api.eresta.test/ontology/ldog.ttl',
                'http://ldog.com/ontology');
        GS::getConnection()
            ->loadIRIintoNamedGraph('http://api.eresta.test/ontology/conversion.ttl',
                'http://ldog.com/ontology/conversion');
        $this->dataCollectionTemplate=$this->getDataCollectionTemplate();
    }
    private function getDataCollectionTemplate():DataCollectionTemplate
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $dataDomain=DataDomain::find($ldogPrefix.DataDomain::HEALTH);
        $dataExportTarget=DataExporterTarget::find($ldogPrefix.DataExporterTarget::MODELLING_ORGANIZATION);

        $identifier='HealthFacility';
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $dataShape=ShapeManager::importFromUrl($this->shapeUrl,$dataDomain->subDomain,$identifier);

        return DataCollectionTemplate::create(
            $identifier,'Health Facilities Template','Health Facilities information in each emirate',
            $dataShape,$cabinet,$dataExportTarget,$dataDomain
        );
    }
    public function testInitiatePipeline():PublishingPipeline
    {
        Storage::fake(config('ldog.storage.disk'));
        $disk=Storage::disk(config('ldog.storage.disk'));
        $conversionsDirectory=config('ldog.storage.directories.root')."/".config('ldog.storage.directories.conversions')."/";
        $csvPath=__DIR__."/../Datasets/PublishingExamples/Facilities/Sheryan_Facility_Detail.csv";

        $pipeline=PublishingPipeline::initiate($this->dataCollectionTemplate,$csvPath);

        $this->assertInstanceOf(PublishingPipeline::class,$pipeline);
        $disk->assertExists($conversionsDirectory.$pipeline->id.'/dataset.csv');
        $disk->assertExists($conversionsDirectory.$pipeline->id.'/dataset.jsonld');
        $disk->assertExists($conversionsDirectory.$pipeline->id.'/config.jsonld');
        $disk->assertExists($conversionsDirectory.$pipeline->id.'/shape.jsonld');
//        $disk->assertExists($conversionsDirectory.$pipeline->id.'/mapping.sparql');

        return $pipeline;
    }

    public function testMakePipeline()
    {
        $csvPath=__DIR__."/../Datasets/PublishingExamples/Facilities/Sheryan_Facility_Detail.csv";
        $expectedPipeline=PublishingPipeline::initiate($this->dataCollectionTemplate,$csvPath);

        $actualPipeline=PublishingPipeline::make($expectedPipeline->id);

        $this->assertEquals($expectedPipeline->shapeJsonLD,$actualPipeline->shapeJsonLD);
    }

    /**
     * @depends  testInitiatePipeline
     */
    public function testGetCsvColumnNames(PublishingPipeline $pipeline)
    {
        $expectedCsvHeader=[
            "unique_id","f_name_english","f_name_arabic","facility_category_name_english",
            "facility_category_name_arabic","facilitysubcategorynameenglish","facilitysubcategorynamearabic",
            "address_line_one","address_line_two_english","address_line_two_arabic","po_box","website",
            "telephone_1","telephone_2","fax","expiry_date","status","area_id","x_coordinate","y_coordinate",
            "email","area_english","area_arabic"
        ];
        $this->assertEquals($expectedCsvHeader,$pipeline->getCsvColumnNames());
    }

    /**
     * @depends testInitiatePipeline
     */
    public function testGetShapePredicates(PublishingPipeline $pipeline)
    {
        $this->assertCount(16,$pipeline->getShapePredicates());
        $this->assertEquals("unique_id",$pipeline->getShapePredicates()[0]->getValue());
        $this->assertEquals("http://www.w3.org/2001/XMLSchema#string",$pipeline->getShapePredicates()[0]->getType());
    }
}