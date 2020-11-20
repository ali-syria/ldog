<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\OrganizationManager\Branch;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\OrganizationManager\Department;
use AliSyria\LDOG\OrganizationManager\IndependentAgency;
use AliSyria\LDOG\OrganizationManager\Institution;
use AliSyria\LDOG\OrganizationManager\Ministry;
use AliSyria\LDOG\OrganizationManager\Organization;
use AliSyria\LDOG\OrganizationManager\OrganizationFactory;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;

class OrganizationFactoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        OntologyManager::importLdogOntology();
    }

    public function testRetrieveByUri()
    {
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $retrievedOrganization=OrganizationFactory::retrieveByUri($cabinet->getUri());

        $this->assertInstanceOf(Cabinet::class,$retrievedOrganization);
        $this->assertEquals($cabinet,$retrievedOrganization);
    }
    public function testCreateOganizationUsingFactory()
    {

    }

    /**
     * @dataProvider ldogClassMappingsProvider
     */
    public function testResolveLdogClassUriToClass(string $uri,string $class)
    {
        $retrievedClass=OrganizationFactory::resolveLdogClassUriToClass($uri);

        $this->assertEquals($class,$retrievedClass);
    }

    public function ldogClassMappingsProvider():array
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        return [
            'Cabinet'=>  [
                $ldogPrefix."Cabinet",Cabinet::class,
            ],
            'Ministry'=>  [
                $ldogPrefix."Ministry",Ministry::class,
            ],
            'IndependentAgency'=>  [
                $ldogPrefix."IndependentAgency",IndependentAgency::class,
            ],
            'Institution'=>  [
                $ldogPrefix."Institution",Institution::class,
            ],
            'Department'=>  [
                $ldogPrefix."Department",Department::class,
            ],
            'Branch'=>  [
                $ldogPrefix."Branch",Branch::class,
            ],
        ];
    }

}