<?php


namespace AliSyria\LDOG\Tests\Unit;


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
}