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

    public function testGetId()
    {
        $id='55556';
        $name='john doe';
        $employee=Employee::create($this->createCabinet(),$this->createLoginAccount('ali','secret'),
            '55556',$name,'working on it department');

        $this->assertEquals($id,$employee->getId());
    }

    public function testGetName()
    {
        $name='john doe';
        $employee=Employee::create($this->createCabinet(),$this->createLoginAccount('ali','secret'),
            '55556',$name,'working on it department');

        $this->assertEquals($name,$employee->getName());
    }

    public function testGetDescription()
    {
        $name='john doe';
        $description='working on it department';

        $employee=Employee::create($this->createCabinet(),$this->createLoginAccount('ali','secret'),
            '55556',$name,$description);

        $this->assertEquals($description,$employee->getDescription());
    }

    public function testGetOrganization()
    {
        $name='john doe';
        $description='working on it department';
        $organization=$this->createCabinet();

        $employee=Employee::create($organization,$this->createLoginAccount('ali','secret'),
            '55556',$name,$description);

        $this->assertEquals($organization,$employee->getOrganization());
    }
    public function testGetLoginAccount()
    {
        $name='john doe';
        $description='working on it department';
        $organization=$this->createCabinet();
        $loginAccount=$this->createLoginAccount('ali','secret');

        $employee=Employee::create($organization,$loginAccount,'55556',$name,$description);

        $this->assertEquals($loginAccount,$employee->getLoginAccount());
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

    private function createCabinet():Cabinet
    {
        return Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
    }
    private function createLoginAccount(string $username,string $password):User
    {
        return  User::create($username,$password);
    }
}