<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\ShapesManager\ShapeManager;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\TemplateBuilder\ReportTemplate;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use AliSyria\LDOG\Utilities\LdogTypes\ReportExportFrequency;

class ReportTemplateTest extends TestCase
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
    private function createReportTemplate():ReportTemplate
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $dataDomain=DataDomain::find($ldogPrefix.DataDomain::HEALTH);
        $dataExportTarget=DataExporterTarget::find($ldogPrefix.DataExporterTarget::ALL_BRANCHES);
        $exportFrequency=ReportExportFrequency::find($ldogPrefix.ReportExportFrequency::DAILY);

        $identifier='Covid19DailyCases';
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $dataShape=ShapeManager::importFromUrl($this->shapeUrl,$dataDomain->subDomain,$identifier);

        $reportTemplate=ReportTemplate::create(
            $identifier,'Covid 19 daily cases Template','Covid 19 daily cases information in each governorate',
            $dataShape,$cabinet,$dataExportTarget,$dataDomain,$exportFrequency
        );

        return $reportTemplate;
    }
    public function testCreateReportTemplate()
    {


        $this->assertInstanceOf(ReportTemplate::class,$this->createReportTemplate());
    }

    public function testRetrieveDataCollectionTemplate()
    {
        $expectedReportTemplate=$this->createReportTemplate();
        $this->assertEquals(ReportTemplate::retrieve($expectedReportTemplate->uri),
            $expectedReportTemplate);
    }
}