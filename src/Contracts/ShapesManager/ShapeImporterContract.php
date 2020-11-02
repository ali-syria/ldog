<?php


namespace AliSyria\LDOG\Contracts\ShapesManager;


use AliSyria\LDOG\ShaclValidator\ShaclValidationReport;

interface ShapeImporterContract
{
    public static function importFromUrl(string $url,string $dataSubDomain,string $prefix):DataShapeContract;
    public static function retrieve(string $uri):?DataShapeContract;
    public static function checkIfExist(string $shapeUri):bool;
    public static function generateUri(string $dataSubDomain,string $prefix):string;
    public static function validateShape(string $shapeUrl):ShaclValidationReport;
}