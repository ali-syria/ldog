<?php


namespace AliSyria\LDOG\UriDereferencer;


use AliSyria\LDOG\Contracts\UriDereferencer\DereferencerContract;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Dereferencer implements DereferencerContract
{
    const MIME_HTML= 'text/html';
    const MIME_XHTML= 'application/xhtml+xml';
    const MIME_RDF_XML= 'application/rdf+xml';
    const MIME_RDF_TURTLE= 'text/turtle';
    const MIME_RDF_N3= 'text/n3';
    const MIME_RDF_N_QUADS= 'application/n-quads';
    const MIME_RDF_N_TRIPLES= 'application/n-triples';
    const MIME_RDF_TRIG= 'application/trig';
    const MIME_RDF_JSON_LD= 'application/ld+json';

    public static function getRDFmimeTypes(): array
    {
        return [
            static::MIME_RDF_JSON_LD,static::MIME_RDF_XML,static::MIME_RDF_TURTLE,
            static::MIME_RDF_N3,static::MIME_RDF_N_TRIPLES,static::MIME_RDF_N_QUADS,
            static::MIME_RDF_TRIG
        ];
    }

    public static function getHTMLmimeTypes(): array
    {
        return [
            static::MIME_HTML,static::MIME_XHTML
        ];
    }

    public static function isRDFmimeType(string $mimeType): bool
    {
        return in_array($mimeType,static::getRDFmimeTypes());
    }

    public static function isHTMLmimeType(string $mimeType): bool
    {
        return in_array($mimeType,static::getHTMLmimeTypes());
    }

    public static function resolveRealResource(Request $request,string $sector,
        string $concept,string $reference):string
    {
        $resourceUriBuilder=URI::realResource($sector,$concept,$reference);
        $uri=null;

        if($request->wantsRDF())
        {
            $uri=$resourceUriBuilder->getDataUri();
        }
        else
        {
            $uri=$resourceUriBuilder->getHtmlUri();
        }

        return (string)$uri;
    }

    public static function resourceToRdfResponse(string $sector,string $concept,string $reference,
         string $mimeType): Response
    {
        $resourceUriBuilder=URI::realResource($sector,$concept,$reference);
        $resourceDescription=GS::getConnection()->describeResource($resourceUriBuilder->getResourceUri(),
            $mimeType);

        return response($resourceDescription->getBody())
            ->withHeaders([
                'Content-Type'=>$resourceDescription->getMimeType()
            ]);
    }

    public static function resourceToHtmlResponse(string $sector,string $concept,string $reference,
          string $mimeType): Response
    {
        $resourceUriBuilder=URI::realResource($sector,$concept,$reference);

        $resultSet=static::getHtmlDataAboutResource($resourceUriBuilder->getResourceUri());

        return response()->view('ldog::resource.page',[
            'results'=>$resultSet
        ])
            ->withHeaders([
                'Content-Type'=>'text/html'
            ]);
    }

    public static function resolveSubRealResource(Request $request, string $sector, string $concept,
        string $reference, string $subConcept, string $subReference)
    {
        $resourceUriBuilder=URI::realResource($sector,$concept,$reference,$subConcept,$subReference);
        $uri=null;

        if($request->wantsRDF())
        {
            $uri=$resourceUriBuilder->getSubDataUri();
        }
        else
        {
            $uri=$resourceUriBuilder->getSubHtmlUri();
        }

        return (string)$uri;
    }

    public static function subResourceToRdfResponse(string $sector, string $concept, string $reference,
        string $subConcept, string $subReference, string $mimeType): Response
    {
        $resourceUriBuilder=URI::realResource($sector,$concept,$reference,$subConcept,$subReference);
        $resourceDescription=GS::getConnection()->describeResource($resourceUriBuilder->getSubResourceUri(),
            $mimeType);

        return response($resourceDescription->getBody())
            ->withHeaders([
                'Content-Type'=>$resourceDescription->getMimeType()
            ]);
    }

    public static function subResourceToHtmlResponse(string $sector, string $concept, string $reference,
         string $subConcept, string $subReference, string $mimeType): Response
    {
        $resourceUriBuilder=URI::realResource($sector,$concept,$reference,$subConcept,$subReference);

        $resultSet=static::getHtmlDataAboutResource($resourceUriBuilder->getSubResourceUri());

        return response()->view('ldog::resource.page',[
            'results'=>$resultSet
        ])
            ->withHeaders([
                'Content-Type'=>'text/html'
            ]);
    }

    private static function getHtmlDataAboutResource(string $uri)
    {
        $resultSet=GS::getConnection()->jsonQuery("
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
           
            select ?property ?propertyText ?value ?valueText 
               where {
                   <$uri> ?property ?value.
                   optional {
                       ?property rdfs:label ?propertyText.                     
                   }
                   optional {
                        ?value rdfs:label ?valueText.
                   }
               }            
        ");

        return $resultSet;
    }

}