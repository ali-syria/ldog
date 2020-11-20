<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\BatchImporter\DataCollection;
use AliSyria\LDOG\BatchImporter\Report;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\OrganizationManager\Employee;
use AliSyria\LDOG\OrganizationManager\Ministry;
use AliSyria\LDOG\OrganizationManager\Organization;
use AliSyria\LDOG\ShapesManager\ShapeManager;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\TemplateBuilder\ReportTemplate;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use AliSyria\LDOG\Utilities\LdogTypes\ReportExportFrequency;

class ReportTest extends TestCase
{
    protected string $shapeUrl;
    public Organization $cabinet;

    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();

        OntologyManager::importLdogOntology();
        OntologyManager::importConversionOntology();
        $this->shapeUrl=UriBuilder::convertRelativeFilePathToUrl(__DIR__.'/../Datasets/Shapes/HealthFacility.ttl');
        $this->cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
    }

    private function createReport():Report
    {
        $conversionId="083a271f-968a-40aa-8205-780516a1fa93";
        $label="Health facilities in dubai";
        $description=null;
        $conversionPath=__DIR__."/../Datasets/Conversions/083a271f-968a-40aa-8205-780516a1fa93/config.jsonld";
        $datasetPath=__DIR__."/../Datasets/Conversions/083a271f-968a-40aa-8205-780516a1fa93/dataset.jsonld";

        $ministryHealth=Ministry::create($this->cabinet,'Ministry Of Health','The Health Ministry of Syria',
            'http://assets.cabinet.sy/health/logo.png');
        $employee=Employee::create($ministryHealth,$this->createLoginAccount('ali','secret'),
            '55556','ali ali','working on it department');

        return Report::create($conversionId,$conversionPath,$datasetPath,
            $label,$description,$this->getReportTemplate(),$ministryHealth,$employee,now()->subDays(20),now()->subDay());
    }
    private function createLoginAccount(string $username,string $password):User
    {
        return  User::create($username,$password);
    }
    private function getReportTemplate():ReportTemplate
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $dataDomain=DataDomain::find($ldogPrefix.DataDomain::HEALTH);
        $dataExportTarget=DataExporterTarget::find($ldogPrefix.DataExporterTarget::MODELLING_ORGANIZATION);

        $identifier='HealthFacility';
        $dataShape=ShapeManager::importFromUrl($this->shapeUrl,$dataDomain->subDomain,$identifier);

        return ReportTemplate::create(
            $identifier,'Health Facilities Template','Health Facilities information in each emirate',
            $dataShape,$this->cabinet,$dataExportTarget,$dataDomain,ReportExportFrequency::find($ldogPrefix.ReportExportFrequency::MONTHLY)
        );
    }
    public function testCreateReport()
    {
        $this->assertInstanceOf(Report::class,$this->createReport());
    }
    public function testRetrieveReport()
    {
        $report=$this->createReport();
        $this->assertEquals($report,Report::retrieve($report->uri));
    }
}