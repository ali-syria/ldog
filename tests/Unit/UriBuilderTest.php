<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\Tests\TestCase;

class UriBuilderTest extends TestCase
{
    private $domain;

    public function setUp(): void
    {
        parent::setUp();

        $this->domain='data.sy';
        config([
            'ldog.domain'=>$this->domain
        ]);
        GS::getConnection()->clearAll();
    }

    public function testTopLevelDomainCanBeConfigured()
    {
        $domain='data.lb';

        config([
            'ldog.domain'=>$domain
        ]);

        $this->assertTrue(URI::realResource('topography','City','tartous')->getTopLevelDomain()==$domain);
    }
    public function testRealResourcePathCanBeGenerated()
    {
        $expected='resoucre/city/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('topography','City','tartous')
                ->getResourcePath()
        );
    }
    public function testRealResourceHtmlPathCanBeGenerated()
    {
        $expected='page/city/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('topography','City','tartous')
                ->getHtmlPath()
        );
    }
    public function testRealResourceDataPathCanBeGenerated()
    {
        $expected='data/city/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('topography','City','tartous')
                ->getDataPath()
        );
    }
    public function testRealResourceUriCanBeGenerated()
    {
        $expected='http://topography.'.$this->domain.'/resoucre/city/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('topography','City','tartous')
                ->getResourceUri()
        );
    }
    public function testRealResourceHtmlUriCanBeGenerated()
    {
        $expected='http://topography.'.$this->domain.'/page/city/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('topography','City','tartous')
                ->getHtmlUri()
        );
    }
    public function testRealResourceDataUriCanBeGenerated()
    {
        $expected='http://topography.'.$this->domain.'/data/city/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('topography','City','tartous')
                ->getDataUri()
        );
    }

    public function testSubRealResourcePathCanBeGenerated()
    {
        $expected='resoucre/institution/free-zones/branch/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('organizations','Institution','free-zones','Branch','tartous')
                ->getSubResourcePath()
        );
    }
    public function testSubRealResourceHtmlPathCanBeGenerated()
    {
        $expected='page/institution/free-zones/branch/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('organizations','Institution','free-zones','Branch','tartous')
                ->getSubHtmlPath()
        );
    }
    public function testSubRealResourceDataPathCanBeGenerated()
    {
        $expected='data/institution/free-zones/branch/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('organizations','Institution','free-zones','Branch','tartous')
                ->getSubDataPath()
        );
    }
    public function testSubRealResourceUriCanBeGenerated()
    {
        $expected='http://organizations.'.$this->domain.'/resoucre/institution/free-zones/branch/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('organizations','Institution','free-zones','Branch','tartous')
                ->getSubResourceUri()
        );
    }
    public function testSubRealResourceHtmlUriCanBeGenerated()
    {
        $expected='http://organizations.'.$this->domain.'/page/institution/free-zones/branch/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('organizations','Institution','free-zones','Branch','tartous')
                ->getSubHtmlUri()
        );
    }
    public function testRealSubResourceDataUriCanBeGenerated()
    {
        $expected='http://organizations.'.$this->domain.'/data/institution/free-zones/branch/tartous';
        $this->assertEquals(
            $expected,
            URI::realResource('organizations','Institution','free-zones','Branch','tartous')
                ->getSubDataUri()
        );
    }

    public function testOntologyBaseUriCanBeGenerated()
    {
        $expected='http://topography.'.$this->domain.'/ontology/administrative_division#';
        $this->assertEquals(
            $expected,
            URI::ontology('topography','administrative_division')
                ->getBasueUri()
        );
    }
    public function testOntologyResourceUriCanBeGenerated()
    {
        $expected='http://topography.'.$this->domain.'/ontology/administrative_division#City';
        $this->assertEquals(
            $expected,
            URI::ontology('topography','administrative_division')
                ->getResourceUri('City')
        );
    }
    public function testDataShapeBaseUriCanBeGenerated()
    {
        $expected='http://topography.'.$this->domain.'/data_shape/city#';
        $this->assertEquals(
            $expected,
            URI::dataShape('topography','city')
                ->getBasueUri()
        );
    }
    public function testDataShapeResourceUriCanBeGenerated()
    {
        $expected='http://topography.'.$this->domain.'/data_shape/city#name';
        $this->assertEquals(
            $expected,
            URI::dataShape('topography','city')
                ->getResourceUri('name')
        );
    }
    public function testIsUriExist()
    {
        $uriBuilder=URI::realResource('topography','City','tartous');
        $resourceUri=$uriBuilder->getResourceUri();

        GS::getConnection()->rawUpdate(
            "
            PREFIX dst: <http://topography.data.example/ontologies/City#>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            
            INSERT DATA
                    { 
                      GRAPH <http://topography.data.example/cities> {
                                <$resourceUri> a dst:City;
                                               rdfs:label  'Tartous';
                                               rdfs:comment 'City in on syrian coast'  .                      
                      }
                    }"
        );

        $this->assertTrue(URI::isUriExist($resourceUri));
        $this->assertFalse(URI::isUriExist($resourceUri."7899"));
    }
}