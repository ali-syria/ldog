<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\ShapesManager\ShapeManager;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;

class DataCollectionTemplateTest extends TestCase
{
    protected string $shapeUrl;

    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        OntologyManager::importLdogOntology();
        $this->shapeUrl=UriBuilder::convertRelativeFilePathToUrl(__DIR__.'/../Datasets/Shapes/HealthFacility.ttl');
    }

    private function createDataCollectionTemplate():DataCollectionTemplate
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $dataDomain=DataDomain::find($ldogPrefix.DataDomain::HEALTH);
        $dataExportTarget=DataExporterTarget::find($ldogPrefix.DataExporterTarget::MODELLING_ORGANIZATION);

        $identifier='HealthFacility';
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $dataShape=ShapeManager::importFromUrl($this->shapeUrl,$dataDomain->subDomain,$identifier);
        $silkLslSpecs=file_get_contents(__DIR__.'/../Datasets/Silk/spec-LSL.xml');

        $dataCollectionTemplate=DataCollectionTemplate::create(
            $identifier,'Health Facilities Template','Health Facilities information in each emirate',
            $dataShape,$cabinet,$dataExportTarget,$dataDomain,$silkLslSpecs
        );
        return $dataCollectionTemplate;
    }
    public function testCreateDataCollectionTemplate()
    {
        $this->assertInstanceOf(DataCollectionTemplate::class,$this->createDataCollectionTemplate());
    }

    public function testRetrieveDataCollectionTemplate()
    {
        $expectedDataCollectionTemplate=$this->createDataCollectionTemplate();
        $this->assertEquals(DataCollectionTemplate::retrieve($expectedDataCollectionTemplate->uri),
            $expectedDataCollectionTemplate);
    }
}