<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\OrganizationManager\Organization;
use AliSyria\LDOG\OrganizationManager\OrganizationFactory;
use AliSyria\LDOG\Tests\TestCase;

class OrganizationFactoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
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
    public function testResolveLdogClassUriToClass()
    {

    }
}