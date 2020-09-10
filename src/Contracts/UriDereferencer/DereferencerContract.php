<?php


namespace AliSyria\LDOG\Contracts\UriDereferencer;


use Illuminate\Http\Request;

interface DereferencerContract
{
    public static function getRDFmimeTypes(): array;

    public static function getHTMLmimeTypes(): array;

    public static function isRDFmimeType(string $mimeType);

    public static function isHTMLmimeType(string $mimeType);

    public static function resolveRealResource(Request $request,string $sector,
         string $concept,string $reference);
}