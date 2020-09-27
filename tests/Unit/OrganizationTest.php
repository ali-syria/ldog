<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OrganizationManager\Branch;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\OrganizationManager\Department;
use AliSyria\LDOG\OrganizationManager\IndependentAgency;
use AliSyria\LDOG\OrganizationManager\Institution;
use AliSyria\LDOG\OrganizationManager\Ministry;
use AliSyria\LDOG\OrganizationManager\Organization;
use AliSyria\LDOG\Tests\TestCase;

class OrganizationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
    }

    public function testGetName()
    {

    }
    public function testSetName()
    {

    }
    public function testGetDescription()
    {

    }
    public function testSetDescription()
    {

    }
    public function testGetLogoUrl()
    {

    }
    public function testSetLogoUrl()
    {

    }
    public function testParentOrganization()
    {

    }
    public function testChildOrganizations()
    {

    }
    public function testEmployees()
    {

    }
    public function testAdmin()
    {

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