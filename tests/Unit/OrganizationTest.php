<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\OrganizationManager\Branch;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\OrganizationManager\Department;
use AliSyria\LDOG\OrganizationManager\Employee;
use AliSyria\LDOG\OrganizationManager\IndependentAgency;
use AliSyria\LDOG\OrganizationManager\Institution;
use AliSyria\LDOG\OrganizationManager\Ministry;
use AliSyria\LDOG\OrganizationManager\Organization;
use AliSyria\LDOG\Tests\TestCase;
use Illuminate\Support\Collection;

class OrganizationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        OntologyManager::importLdogOntology();
    }

    public function testGetName()
    {
        $cabinetName='Syrian Cabinet';
        $cabinet=Cabinet::create(null,$cabinetName,'The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');

        $this->assertEquals($cabinetName,$cabinet->getName());
    }

    public function testGetDescription()
    {
        $cabinetName='Syrian Cabinet';
        $cabinetDescription='The Cabinet of Syria';
        $cabinet=Cabinet::create(null,$cabinetName,$cabinetDescription,
            'http://assets.cabinet.sy/logo.png');

        $this->assertEquals($cabinetDescription,$cabinet->getDescription());
    }

    public function testGetLogoUrl()
    {
        $cabinetName='Syrian Cabinet';
        $cabinetDescription='The Cabinet of Syria';
        $cabinetLogoUrl='http://assets.cabinet.sy/logo.png';
        $cabinet=Cabinet::create(null,$cabinetName,$cabinetDescription,$cabinetLogoUrl);

        $this->assertEquals($cabinetLogoUrl,$cabinet->getLogoUrl());
    }

    public function testParentOrganization()
    {
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $ministryHealth=Ministry::create($cabinet,'Ministry Of Health','The Health Ministry of Syria',
            'http://assets.cabinet.sy/health/logo.png');
        $ministryEducation=Ministry::create($cabinet,'Ministry Of Education','The Education Ministry of Syria',
            'http://assets.cabinet.sy/education/logo.png');
        $hospitals=Institution::create($ministryHealth,'Hospitals Management Institution',
            'This institution is responsible of managing syrian hospitals',
            'http://assets.health.sy/hospitals/logo.png');
        $this->assertEquals($cabinet,$ministryHealth->parentOrganization());
        $this->assertEquals($ministryHealth,$hospitals->parentOrganization());
    }
    public function testChildOrganizations()
    {
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $ministryHealth=Ministry::create($cabinet,'Ministry Of Health','The Health Ministry of Syria',
            'http://assets.cabinet.sy/health/logo.png');
        $ministryEducation=Ministry::create($cabinet,'Ministry Of Education','The Education Ministry of Syria',
            'http://assets.cabinet.sy/education/logo.png');
        $hospitals=Institution::create($ministryHealth,'Hospitals Management Institution',
            'This institution is responsible of managing syrian hospitals',
            'http://assets.health.sy/hospitals/logo.png');

        $expectedChildOrganizations=new Collection([
            $ministryHealth,$ministryEducation
        ]);

        $this->assertEquals($expectedChildOrganizations,$cabinet->childOrganizations());
    }
    public function testGetOrganizationEmployees()
    {
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');

        $loginAccount1=User::create('ali','secret');
        $employee1=Employee::create($cabinet,$loginAccount1,'55556','john doe',
            'working on it department');

        $loginAccount2=User::create('mohamad','secret');
        $employee2=Employee::create($cabinet,$loginAccount2,'78920','john doe',
            'working on human resources department');

        $actualEmployees=$cabinet->employees();
        $expectedEmployees=new Collection([
            $employee1,$employee2
        ]);

        $this->assertEqualsCanonicalizing($expectedEmployees,$actualEmployees);
    }

    public function testCreateCabinet():Cabinet
    {
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $this->assertInstanceOf(Cabinet::class,$cabinet);

        return $cabinet;
    }

    /**
     * @depends testCreateCabinet
     * @return Ministry
     */
    public function testCreateMinistry(Cabinet $cabinet):Ministry
    {
        $ministryHealth=Ministry::create($cabinet,'Ministry Of Health','The Health Ministry of Syria',
            'http://assets.cabinet.sy/health/logo.png');

        $this->assertInstanceOf(Ministry::class,$ministryHealth);

        return $ministryHealth;
    }

    /**
     * @depends testCreateCabinet
     * @return IndependentAgency
     */
    public function testCreateIndependentAgecny(Cabinet $cabinet):IndependentAgency
    {
        $casi=IndependentAgency::create($cabinet,'Central Authority for Supervision and Inspection',
            'Central Authority for Supervision and Inspection',
            'http://assets.cabinet.sy/casi/logo.png');

        $this->assertInstanceOf(IndependentAgency::class,$casi);

        return $casi;
    }

    /**
     * @depends testCreateMinistry
     * @return Ministry
     */
    public function testCreateInstitution(Ministry $ministry):Institution
    {
        $hospitals=Institution::create($ministry,'Hospitals Management Institution',
            'This institution is responsible of managing syrian hospitals',
            'http://assets.health.sy/hospitals/logo.png');

        $this->assertInstanceOf(Institution::class,$hospitals);

        return $hospitals;
    }
    /**
     * @depends testCreateInstitution
     * @return Branch
     */
    public function testCreateBranch(Institution $institution):Branch
    {
        $tartousNationalhospital=Branch::create($institution,'Tartous National Hospital',
            'It was established in 1990, and it consists of the following departments: Emergency, Cardiac',
            'http://assets.tartous-hospital.sy/logo.png');

        $this->assertInstanceOf(Branch::class,$tartousNationalhospital);

        return $tartousNationalhospital;
    }

    /**
     * @depends testCreateMinistry
     * @return Department
     */
    public function testCreateDepartment(Ministry $ministry):Department
    {
        $pandemicsDepartment=Department::create($ministry,'Department Of Pandemics',
            'The DP department is responsible of dealing with pandemics according of the World Health Organization recommendations',
            'http://assets.health.sy/logo.png');

        $this->assertInstanceOf(Department::class,$pandemicsDepartment);

        return $pandemicsDepartment;
    }

}