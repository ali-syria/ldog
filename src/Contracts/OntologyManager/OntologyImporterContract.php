<?php


namespace AliSyria\LDOG\Contracts\OntologyManager;


interface OntologyImporterContract
{
    public static function importFromUrl(string $url,string $sector,string $prefix,string $namespace):void;
    public static function checkIfExist(string $ontologyUri):bool;
    public static function generateUri(string $sector, string $prefix):string;
}