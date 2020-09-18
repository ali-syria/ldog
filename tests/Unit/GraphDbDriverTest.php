<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Contracts\GraphStore\ResourceDescriptionContract;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\GraphStore\GraphDbDriver;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriDereferencer\Dereferencer;
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
        $results=json_decode($results,true);
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

        $results=json_decode($results,true);
        $this->assertEquals(0,count($results['results']['bindings']));
    }

    public function testRawUpdate()
    {

        $this->graphDB->rawUpdate(
            '
            PREFIX dc: <http://purl.org/dc/elements/1.1/>
            
            INSERT DATA
                    { 
                      GRAPH <http://example/books> {
                                <http://example/book1> dc:title "A new book" ;
                                             dc:creator "A.N.Other" .                      
                      }
                    }'
        );
        $resultSet=$this->graphDB->jsonQuery('
                   PREFIX dc: <http://purl.org/dc/elements/1.1/>
                   
                   select ?book
                   where {
                        GRAPH <http://example/books> {<http://example/book1> dc:title ?book}
                        }       
        ');

       foreach ($resultSet as $result)
       {
           $this->assertEquals('A new book',$result->book->getValue());
           break;
       }
    }
    /**
     * @dataProvider rdfMimeTypesProvider
     */
    public function testDescribeResource(string $mimeType)
    {
        $uriBuilder=URI::realResource('topography','City','tartous');
        $resourceUri=$uriBuilder->getResourceUri();

        $this->graphDB->rawUpdate(
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
        $resourceDescription=$this->graphDB->describeResource($resourceUri,$mimeType);
        $this->assertInstanceOf(ResourceDescriptionContract::class,$resourceDescription);
        $this->assertStringContainsString($mimeType,$resourceDescription->getMimeType());
        $this->assertStringContainsString($resourceUri,$resourceDescription->getBody());
    }

    public function rdfMimeTypesProvider():array
    {
        $data=[];
        foreach (Dereferencer::getRDFmimeTypes() as $mimeType)
        {
            $data[$mimeType]=[$mimeType];
        }

        return $data;
    }
}