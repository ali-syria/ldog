<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\BatchImporter\DataCollection;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\OrganizationManager\Employee;
use AliSyria\LDOG\OrganizationManager\Ministry;
use AliSyria\LDOG\OrganizationManager\Organization;
use AliSyria\LDOG\PublishingPipeline\PublishingPipeline;
use AliSyria\LDOG\ShapesManager\ShapeManager;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use Illuminate\Support\Str;

class DataCollectionTest extends TestCase
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

    private function createDataCollection():DataCollection
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

        return DataCollection::create($conversionId,$conversionPath,$datasetPath,
            $label,$description,$this->getDataCollectionTemplate(),$ministryHealth,$employee,now()->subDays(20),now()->subDay());
    }
    private function createLoginAccount(string $username,string $password):User
    {
        return  User::create($username,$password);
    }
    private function getDataCollectionTemplate():DataCollectionTemplate
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $dataDomain=DataDomain::find($ldogPrefix.DataDomain::HEALTH);
        $dataExportTarget=DataExporterTarget::find($ldogPrefix.DataExporterTarget::MODELLING_ORGANIZATION);

        $identifier='HealthFacility';
        $dataShape=ShapeManager::importFromUrl($this->shapeUrl,$dataDomain->subDomain,$identifier);

        return DataCollectionTemplate::create(
            $identifier,'Health Facilities Template','Health Facilities information in each emirate',
            $dataShape,$this->cabinet,$dataExportTarget,$dataDomain
        );
    }
    public function testCreateDataCollection()
    {
        $this->assertInstanceOf(DataCollection::class,$this->createDataCollection());
    }
    public function testRetrieveDataCollection()
    {
        $dataCollection=$this->createDataCollection();
        $this->assertEquals($dataCollection,DataCollection::retrieve($dataCollection->uri));
    }
}