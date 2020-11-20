<?php


namespace AliSyria\LDOG\Tests\Unit\LdogTypes;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;

class DataDomainTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        OntologyManager::importLdogOntology();
    }

    /**
     * @dataProvider dataDomainsProvider
     */
    public function testGetAllDataDomains(string $uri,string $label,string $subDomain)
    {
        $dataDomain=DataDomain::all()->where('uri',$uri)->first();
        $this->assertEquals(new DataDomain($uri,$label,null,$subDomain),$dataDomain);
    }


    public function dataDomainsProvider():array
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        return [
            'Education'=>[$ldogPrefix.'Education','Education','education'],
            'General Info'=>[$ldogPrefix.'GeneralInfo','General Info','general'],
            'Government'=>[$ldogPrefix.'Government','Government','government'],
            'Health'=>[$ldogPrefix.'Health','Health','health'],
            'Real Estate'=>[$ldogPrefix.'RealEstate','Real Estate','real-estate'],
            'Tourism'=>[$ldogPrefix.'Tourism','Tourism','tourism'],
            'Transport'=>[$ldogPrefix.'Transport','Transport','transport'],
        ];
    }
}