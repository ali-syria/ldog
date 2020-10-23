<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\ShapesManager\ShapeManager;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;

class DataCollectionTemplateTest extends TestCase
{
    protected string $shapeUrl="http://api.eresta.test/shapes/HealthFacility.ttl";

    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        GS::getConnection()
            ->loadIRIintoNamedGraph('http://api.eresta.test/ontology/ldog.ttl','http://ldog.com/ontology');
    }

    public function testCreateDataCollectionTemplate()
    {
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

        $this->assertInstanceOf(DataCollectionTemplate::class,$dataCollectionTemplate);
    }
}