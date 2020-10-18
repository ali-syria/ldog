<?php


namespace AliSyria\LDOG\ShapesManager;


use AliSyria\LDOG\Contracts\ShapesManager\ShapeImporterContract;

class ShapeManager implements ShapeImporterContract
{

    public static function importFromUrl(string $url, string $dataSubDomain): void
    {
        // TODO: Implement importFromUrl() method.
    }

    public static function checkIfExist(string $shapeUri): bool
    {
        // TODO: Implement checkIfExist() method.
    }

    public static function generateUri(string $dataSubDomain): string
    {
        // TODO: Implement generateUri() method.
    }

    public static function validateShape(string $shapeUri): bool
    {
        // TODO: Implement validateShape() method.
    }
}