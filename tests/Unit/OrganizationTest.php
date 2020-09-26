<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\OrganizationManager\Department;
use AliSyria\LDOG\OrganizationManager\Ministry;
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
    public function testCreateOrganization()
    {
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $ministryHealth=Ministry::create($cabinet,'Ministry Of Health','The Health Ministry of Syria',
            'http://assets.cabinet.sy/health/logo.png');
        $pandemicsDepartment=Department::create($ministryHealth,'Department Of Pandemics',
            'The DP department is responsible of dealing with pandemics according of the World Health Organization recommendations',
            'http://assets.health.sy/logo.png');
        $this->assertInstanceOf(Cabinet::class,$cabinet);
    }
}