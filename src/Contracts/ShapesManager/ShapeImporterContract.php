<?php


namespace AliSyria\LDOG\Contracts\ShapesManager;


interface ShapeImporterContract
{
    public static function importFromUrl(string $url,string $dataSubDomain):void;
    public static function checkIfExist(string $shapeUri):bool;
    public static function generateUri(string $dataSubDomain):string;
    public static function validateShape(string $shapeUri):bool;
}