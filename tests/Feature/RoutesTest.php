<?php


namespace AliSyria\LDOG\Tests\Feature;


use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriDereferencer\Dereferencer;

class RoutesTest extends TestCase
{
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
    public function testRealResourceRequestAcceptHtmlIsRedirectedToDataUri(string $mimeType)
    {
        $uriBuilder=URI::realResource('topography','City','tartous');
        $resourceUri=$uriBuilder->getResourceUri();

        $htmlUri=$uriBuilder->getHtmlUri();

        $this->withHeader('accept',$mimeType)
            ->get($resourceUri)
            ->assertRedirect($htmlUri);
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