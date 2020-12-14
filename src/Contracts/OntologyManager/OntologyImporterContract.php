<?php


namespace AliSyria\LDOG\Contracts\OntologyManager;


use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use Illuminate\Support\Collection;

interface OntologyImporterContract
{
    public static function importFromUrl(string $url,DataDomain $dataDomain,string $prefix,string $namespace,string $description):void;
    public static function checkIfExist(string $ontologyUri):bool;
    public static function generateUri(string $sector, string $prefix):string;
    public static function getAll():Collection;
}