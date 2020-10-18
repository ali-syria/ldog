<?php


namespace AliSyria\LDOG\Tests\Unit\LdogTypes;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;

class DataExporterTargetTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        GS::getConnection()
            ->loadIRIintoNamedGraph('http://api.eresta.test/ontology/ldog.ttl','http://ldog.com/ontology');
    }

    /**
     * @dataProvider dataExporterTargetsProvider
     */
    public function testGetAllDataExporterTargets(string $uri,string $label)
    {
        $dataExporterTarget=DataExporterTarget::all()->where('uri',$uri)->first();
        $this->assertEquals(new DataExporterTarget($uri,$label),$dataExporterTarget);
    }

    public function dataExporterTargetsProvider():array
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        return [
            'All Branches'=>[$ldogPrefix.'AllBranches','All branches'],
            'All departments'=>[$ldogPrefix.'AllDepartments','All departments'],
            'All branches and departments'=>[$ldogPrefix.'AllSectors','All branches and departments'],
            'Modelling Organization'=>[$ldogPrefix.'ModellingOrganization','Modelling Organization'],
        ];
    }
}