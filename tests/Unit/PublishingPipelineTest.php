<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\PublishingPipeline\PublishingPipeline;
use AliSyria\LDOG\ShapesManager\ShapeManager;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use Illuminate\Support\Facades\Storage;
use ML\JsonLD\Node;

class PublishingPipelineTest extends TestCase
{
    protected string $shapeUrl="http://api.eresta.test/shapes/HealthFacility.ttl";
    protected DataCollectionTemplate $dataCollectionTemplate;
    protected array $mappings=[
        'http://health.data.ae/ontology/HealthFacility#uniqueID'=>'unique_id',
        'http://health.data.ae/ontology/HealthFacility#name'=>'f_name_english',
        'http://health.data.ae/ontology/HealthFacility#category'=>'facility_category_name_english',
        'http://health.data.ae/ontology/HealthFacility#subCategory'=>'facilitysubcategorynameenglish',
        'http://health.data.ae/ontology/HealthFacility#address_line_one'=>'address_line_one',
        'http://health.data.ae/ontology/HealthFacility#address_line_two'=>'address_line_two_english',
        'http://health.data.ae/ontology/Address#postalCode'=>'po_box',
        'http://health.data.ae/ontology/Contact#website'=>'website',
        'http://health.data.ae/ontology/Contact#telephone'=>'telephone_1',
        'http://health.data.ae/ontology/HealthFacility#expiry_date'=>'expiry_date',
        'http://health.data.ae/ontology/HealthFacility#status'=>'status',
        'http://health.data.ae/ontology/HealthFacility#area'=>'area_id',
        'http://health.data.ae/ontology/HealthFacility#email'=>'email',
        'http://health.data.ae/ontology/HealthFacility#latitude'=>'x_coordinate',
        'http://health.data.ae/ontology/HealthFacility#longitude'=>'y_coordinate',
    ];

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

    public function testMakePipeline():PublishingPipeline
    {
        $csvPath=__DIR__."/../Datasets/PublishingExamples/Facilities/Sheryan_Facility_Detail.csv";
        $expectedPipeline=PublishingPipeline::initiate($this->dataCollectionTemplate,$csvPath);

        $actualPipeline=PublishingPipeline::make($expectedPipeline->id);

        $this->assertEquals($expectedPipeline->shapeJsonLD,$actualPipeline->shapeJsonLD);

        return $actualPipeline;
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
        $this->assertCount(15,$pipeline->getShapePredicates()->toArray());
        $this->assertEquals("unique_id",$pipeline->getShapePredicates()->first()->name);
        $this->assertEquals("http://www.w3.org/2001/XMLSchema#integer",$pipeline->getShapePredicates()
            ->first()->dataType);
    }

    /**
     * @depends testInitiatePipeline
     */
    public function testGetShapeObjectPredicates(PublishingPipeline $pipeline)
    {
        $prefix='http://health.data.ae/ontology/HealthFacility#';
        $this->assertEquals(2,$pipeline->getShapeObjectPredicates()->count());
        $this->assertEqualsCanonicalizing([$prefix.'HealthFacilityCategory',$prefix.'HealthFacilitySubCategory'],
            $pipeline->getShapeObjectPredicates()->pluck('objectClassUri')->toArray());
    }

    /**
     * @depends testInitiatePipeline
     */
    public function testGetShapeDataPredicates(PublishingPipeline $pipeline)
    {
        $this->assertEquals(13,$pipeline->getShapeDataPredicates()->count());
        $this->assertEqualsCanonicalizing([
            'unique_id','name','address line one','address line two','postal code','website',
            'telephone','expiry date','status','area','email','latitude','longitude'
        ],
        $pipeline->getShapeDataPredicates()->pluck('name')->toArray());
    }

    /**
     * @depends testMakePipeline
     */
    public function testGenerateResourceNode(PublishingPipeline $pipeline)
    {
        $expectedUri=URI::realResource($pipeline->dataTemplate->dataDomain->subDomain,$pipeline->getTargetClassName(),66444)
            ->getResourceUri();
        $expectedNode=new Node($pipeline->dataJsonLD->getGraph(),$expectedUri);
        $actualNode=$pipeline->generateResourceNode($pipeline->dataJsonLD->getGraph(),
            $pipeline->getTargetClassName(),66444);
        $this->assertEquals($expectedNode,$actualNode);
    }
    /**
     * @depends testMakePipeline
     */
    public function testMapColumnsToPredicates(PublishingPipeline $pipeline)
    {
        $pipeline->mapColumnsToPredicates($this->mappings);
        $graph=$pipeline->configJsonLD->getGraph();
        $rawRdfGenerationNode=$graph->getNodesByType(UriBuilder::PREFIX_CONVERSION.'RawRdfGeneration')[0];

        $columnPredicateMappingNodes=$rawRdfGenerationNode->getProperty(UriBuilder::PREFIX_CONVERSION."hasColumnPredicateMapping");
        $this->assertEquals(count($this->mappings),count($columnPredicateMappingNodes));
        foreach ($columnPredicateMappingNodes as $columnPredicateMappingNode)
        {
            $predicateUri=$columnPredicateMappingNode->getProperty(UriBuilder::PREFIX_CONVERSION.'predicate')->getId();
            $actualColumnName=$columnPredicateMappingNode->getProperty(UriBuilder::PREFIX_CONVERSION.'columnName')->getValue();
            $expectedMappingColumnName=$this->mappings[$predicateUri];
            $this->assertEquals($expectedMappingColumnName,$actualColumnName);
        }

        return $pipeline;
    }
    /**
     * @depends testMakePipeline
     */
    public function testGenerateRawRdf(PublishingPipeline $pipeline)
    {
        $pipeline->generateRawRdf($this->mappings);

    }
    /**
     * @depends testMakePipeline
     */
    public function testGetTargetClassUri(PublishingPipeline $pipeline)
    {
        $this->assertEquals('http://health.data.ae/ontology/HealthFacility#HealthFacility',
            $pipeline->getTargetClassUri());
    }
    /**
     * @depends testMakePipeline
     */
    public function testGetTargetClassName(PublishingPipeline $pipeline)
    {
        $this->assertEquals('HealthFacility',
            $pipeline->getTargetClassName());
    }
    /**
     * @depends testMakePipeline
     */
    public function testGetResourceIdentifierPropertyUri(PublishingPipeline $pipeline)
    {
        $this->assertEquals('http://health.data.ae/ontology/HealthFacility#uniqueID',
            $pipeline->getResourceIdentifierPropertyUri());
    }
    /**
     * @depends testMakePipeline
     */
    public function testGetResourceLabelExpression(PublishingPipeline $pipeline)
    {
        $this->assertEquals('health facility: {name},number: {unique_id}',
            $pipeline->getResourceLabelExpression());
    }

    public function testExtractClassNameFromUri()
    {
        $hashClassUri='http://health.data.ae/ontology/HealthFacility#HealthFacility';
        $this->assertEquals('HealthFacility',PublishingPipeline::extractClassNameFromUri($hashClassUri));

        $slashClassUri='http://health.data.ae/ontology/HealthFacility/HealthFacility';
        $this->assertEquals('HealthFacility',PublishingPipeline::extractClassNameFromUri($slashClassUri));
    }
}