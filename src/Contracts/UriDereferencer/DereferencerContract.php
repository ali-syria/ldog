<?php


namespace AliSyria\LDOG\Contracts\UriDereferencer;


use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface DereferencerContract
{
    public static function getRDFmimeTypes(): array;

    public static function getHTMLmimeTypes(): array;

    public static function isRDFmimeType(string $mimeType);

    public static function isHTMLmimeType(string $mimeType);

    public static function resolveRealResource(Request $request,string $sector,
         string $concept,string $reference);
    public static function resourceToRdfResponse(string $sector,string $concept,string $reference,
                                                 string $mimeType):Response;
    public static function resourceToHtmlResponse(string $sector,string $concept,string $reference,
                                                  string $mimeType):Response;

    public static function resolveSubRealResource(Request $request,string $sector,
          string $concept,string $reference,string $subConcept,string $subReference);
    public static function subResourceToRdfResponse(string $sector,string $concept,string $reference,
        string $subConcept,string $subReference,string $mimeType):Response;
    public static function subResourceToHtmlResponse(string $sector,string $concept,string $reference,
        string $subConcept,string $subReference,string $mimeType):Response;
}