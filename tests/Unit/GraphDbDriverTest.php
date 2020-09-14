<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\GraphStore\GraphDbDriver;
use AliSyria\LDOG\Tests\TestCase;
use EasyRdf\Sparql\Result;
use phpDocumentor\Reflection\Types\True_;

class GraphDbDriverTest extends TestCase
{
    private GraphDbDriver $graphDB;

    public function setUp(): void
    {
        parent::setUp();
        $this->graphDB=app(GraphDbDriver::class)->connect('open');
        $this->graphDB->clearAll();
    }

    public function testRawQuery()
    {
        $this->graphDB
            ->loadIRIintoNamedGraph('http://dbpedia.org/data/Damascus.ttl',
                'http://example/cities');

        $results=$this->graphDB->rawQuery(
            'select ?s ?p ?o 
                   where {?s ?p ?o}
                   limit 2'
        );
        $results=json_decode($results,TRUE);
        $this->assertGreaterThanOrEqual(1,count($results['results']['bindings']));
    }
    public function testJsonQueryInstanceOfEasyRdfResult()
    {
        $this->graphDB
            ->loadIRIintoNamedGraph('http://dbpedia.org/data/Damascus.ttl',
                'http://example/cities');

        $result=$this->graphDB->jsonQuery(
            'select ?s ?p ?o 
                   where {?s ?p ?o}
                   limit 2'
        );

        $this->assertInstanceOf(Result::class,$result);
    }
    public function testLoadIRIintoNamedGraph()
    {
        $this->graphDB
            ->loadIRIintoNamedGraph('http://dbpedia.org/data/Damascus.ttl','http://example/cities');

        $results=$this->graphDB->rawQuery(
            'select ?s ?p ?o 
                   where {?s ?p ?o}
                   limit 2'
        );

        $this->assertGreaterThanOrEqual(1,count($results['results']['bindings']));
    }
    public function testClearNamedGraph()
    {
        $this->graphDB
            ->loadIRIintoNamedGraph('http://dbpedia.org/data/Damascus.ttl','http://example/cities');

        $this->graphDB->clearNamedGraph('http://example/cities');

        $results=$this->graphDB->rawQuery(
            'select ?s ?p ?o
                   where {
                        GRAPH <http://example/cities> {?s ?p ?o}
                        }
                   limit 2'
        );

        $this->assertEquals(0,count($results['results']['bindings']));
    }
}