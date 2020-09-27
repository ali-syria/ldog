<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\OrganizationManager\Employee;
use AliSyria\LDOG\OrganizationManager\Ministry;
use AliSyria\LDOG\Tests\TestCase;

class EmployeeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
    }

    public function testGetName()
    {

    }

    public function testGetDescription()
    {

    }

    public function testGetOrganization()
    {

    }
    public function testGetLoginAccount()
    {

    }
    public function testCreateEmployee():EmployeeContract
    {
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $loginAccount=User::create('ali','secret');
        $employee=Employee::create($cabinet,$loginAccount,'55556','john doe',
            'working on it department');

        $this->assertInstanceOf(Employee::class,$employee);

        return $employee;
    }

    public function testRetrieveByLoginAccount()
    {
        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $loginAccount=User::create('ali','secret');
        $employee=Employee::create($cabinet,$loginAccount,'55556','john doe',
            'working on it department');

        $retrievedEmployee=Employee::retrieveByLoginAccount($loginAccount);

        $this->assertEquals($employee,$retrievedEmployee);
    }
}