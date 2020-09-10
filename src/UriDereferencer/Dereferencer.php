<?php


namespace AliSyria\LDOG\UriDereferencer;


use AliSyria\LDOG\Contracts\UriDereferencer\DereferencerContract;
use AliSyria\LDOG\Facades\URI;
use Illuminate\Http\Request;

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
}