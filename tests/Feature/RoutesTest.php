<?php


namespace AliSyria\LDOG\Tests\Feature;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriDereferencer\Dereferencer;

class RoutesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
    }

    public function testRealResourceRouteIsRedirectedToHtmlPageByDefault()
    {
        $uriBuilder=URI::realResource('topography','City','tartous');

        $resourceUri=$uriBuilder->getResourceUri();
        $htmlUri=$uriBuilder->getHtmlUri();

        $this->withHeader('accept','nothing/nothing')->get($resourceUri)
            ->assertRedirect($htmlUri);
    }

    public function testRealResourceRouteIsRedirectedWithSameRequestHeaders()
    {
        $uriBuilder=URI::realResource('topography','City','tartous');

        $resourceUri=$uriBuilder->getResourceUri();

        $this->withHeader('accept',Dereferencer::MIME_RDF_JSON_LD)
            ->get($resourceUri)
            ->assertHeader('accept',Dereferencer::MIME_RDF_JSON_LD);
    }

    /**
     * @dataProvider rdfMimeTypesProvider
     */
    public function testRealResourceRequestAcceptRdfIsRedirectedToDataUri(string $mimeType)
    {
        $uriBuilder=URI::realResource('topography','City','tartous');
        $resourceUri=$uriBuilder->getResourceUri();

        $dataUri=$uriBuilder->getDataUri();

        $this->withHeader('accept',$mimeType)
            ->get($resourceUri)
            ->assertRedirect($dataUri);
    }

    /**
     * @dataProvider htmlMimeTypesProvider
     */
    public function testRealResourceRequestAcceptHtmlIsRedirectedToHtmlUri(string $mimeType)
    {
        $uriBuilder=URI::realResource('topography','City','tartous');
        $resourceUri=$uriBuilder->getResourceUri();

        $htmlUri=$uriBuilder->getHtmlUri();

        $this->withHeader('accept',$mimeType)
            ->get($resourceUri)
            ->assertRedirect($htmlUri);
    }

    /**
     * @dataProvider rdfMimeTypesProvider
     */
    public function testSubRealResourceRequestAcceptRdfIsRedirectedToDataUri(string $mimeType)
    {
        $uriBuilder=URI::realResource('organizations','Institution','free-zones','Branch','tartous');
        $resourceUri=$uriBuilder->getSubResourceUri();

        $dataUri=$uriBuilder->getSubDataUri();

        $this->withHeader('accept',$mimeType)
            ->get($resourceUri)
            ->assertRedirect($dataUri);
    }

    /**
     * @dataProvider htmlMimeTypesProvider
     */
    public function testSubRealResourceRequestAcceptHtmlIsRedirectedToHtmlUri(string $mimeType)
    {
        $uriBuilder=URI::realResource('organizations','Institution','free-zones','Branch','tartous');
        $resourceUri=$uriBuilder->getSubResourceUri();

        $htmlUri=$uriBuilder->getSubHtmlUri();

        $this->withHeader('accept',$mimeType)
            ->get($resourceUri)
            ->assertRedirect($htmlUri);
    }
    /**
     * @dataProvider rdfMimeTypesProvider
     */
    public function testDataUriIsReturnRdfResponseWithAcceptedMimeType(string $mimeType)
    {
        $uriBuilder=URI::realResource('topography','City','tartous');
        $resourceUri=$uriBuilder->getResourceUri();
        $dataUri=$uriBuilder->getDataUri();

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


        $response=$this->withHeader('accept',$mimeType)
            ->get($dataUri)
            ->assertOk();

        $contentTypeHeader=$response->headers->get('Content-Type');
        $this->assertStringContainsString($mimeType,$contentTypeHeader);
        $this->assertStringContainsString($resourceUri,$response->content());
    }
    /**
     * @dataProvider htmlMimeTypesProvider
     */
    public function testPageUriIsReturnHtmlResponseWithAcceptedMimeType(string $mimeType)
    {
        $uriBuilder=URI::realResource('topography','City','damascus');
        $resourceUri=$uriBuilder->getResourceUri();
        $htmlUri=$uriBuilder->getHtmlUri();
        GS::getConnection()
            ->loadIRIintoNamedGraph('https://www.w3.org/1999/02/22-rdf-syntax-ns.ttl','http://ontologies.data.example/rdf');
        GS::getConnection()->rawUpdate(
            "
            PREFIX dst:  <http://topography.data.example/ontologies/City#>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            PREFIX owl:  <http://www.w3.org/2002/07/owl#>
            PREFIX dbr: <http://dbpedia.org/resource/>
            
            INSERT DATA
            { 
                  GRAPH <http://topography.data.example/cities> {
                        <$resourceUri> a dst:City;
                                       rdfs:label  'Damascus';
                                       rdfs:comment 'City in on syrian coast';
                                       owl:sameAs dbr:Damascus.   
                        owl:sameAs rdfs:label 'same'.
                        dbr:Damascus rdfs:label 'Damascus'                   
                  }
            }
        ");


        $response=$this->withHeader('accept',$mimeType)
            ->get($htmlUri)
            ->assertOk();

        $contentTypeHeader=$response->headers->get('Content-Type');
        $this->assertStringContainsString($mimeType,$contentTypeHeader);
        $this->assertStringContainsString("<html",$response->content());
        $this->assertStringContainsString("Damascus",$response->content());
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

    public function htmlMimeTypesProvider():array
    {
        $data=[];
        foreach (Dereferencer::getHTMLmimeTypes() as $mimeType)
        {
            $data[$mimeType]=[$mimeType];
        }

        return $data;
    }
}