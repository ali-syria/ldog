<?php


namespace AliSyria\LDOG\Contracts\ShapesManager;


use AliSyria\LDOG\ShaclValidator\ShaclValidationReport;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;

interface ShapeImporterContract
{
    public static function importFromUrl(string $url,DataDomain $dataDomain,string $prefix):DataShapeContract;
    public static function retrieve(string $uri):?DataShapeContract;
    public static function checkIfExist(string $shapeUri):bool;
    public static function generateUri(string $dataSubDomain,string $prefix):string;
    public static function validateShape(string $shapeUrl):ShaclValidationReport;
}