<?php


namespace AliSyria\LDOG\ShapesManager;


use AliSyria\LDOG\Contracts\ShapesManager\DataShapeContract;
use AliSyria\LDOG\Contracts\ShapesManager\ShapeImporterContract;
use AliSyria\LDOG\Exceptions\DataShapeAlreadyExist;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\ShaclValidator\JenaShaclValidator;
use AliSyria\LDOG\ShaclValidator\ShaclValidationReport;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ShapeManager implements ShapeImporterContract
{

    public static function importFromUrl(string $url, string $dataSubDomain,string $prefix): DataShape
    {
        $shapeUri=self::generateUri($dataSubDomain,$prefix);

        throw_if(self::checkIfExist($shapeUri),
            new DataShapeAlreadyExist('a shape with same prefix already exists'));

        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        GS::getConnection()->loadIRIintoNamedGraph($url,$shapeUri);

        GS::getConnection()->rawUpdate("
            PREFIX ldog: <$ldogPrefix> 
            
            INSERT DATA {
                GRAPH <$shapeUri> {
                    <$shapeUri> a ldog:DataShape ;
                                 ldog:prefix '$prefix' .
                }
            }
        ");

        return new DataShape($shapeUri);
    }
    public static function retrieve(string $uri): ?DataShapeContract
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $query= "
            PREFIX ldog: <$ldogPrefix> 
            
            SELECT ?prefix
            WHERE {
                    <$uri> a ldog:DataShape ;
                                 ldog:prefix ?prefix .
            }
        ";

        $resultSet=GS::getConnection()->jsonQuery($query);

        foreach ($resultSet as $result)
        {
            return new DataShape($uri);
        }

        return null;
    }

    public static function checkIfExist(string $shapeUri): bool
    {
        return GS::getConnection()->isGraphExist($shapeUri);
    }

    public static function generateUri(string $dataSubDomain,string $prefix): string
    {
        return URI::dataShape($dataSubDomain,$prefix)->getBasueUri();
    }

    public static function validateShape(string $shapeUrl): ShaclValidationReport
    {
        $shapeGraphContent=file_get_contents($shapeUrl);

        $temporaryDirectory=(new TemporaryDirectory())->name(Str::uuid())->create();
        $shapeFileName=Str::uuid().'.ttl';
        $shapeFilePath=$temporaryDirectory->path($shapeFileName);

        File::put($shapeFilePath,$shapeGraphContent);

        $validationReport=app(JenaShaclValidator::class)
            ->basicShapeValidation($shapeFilePath);

        $temporaryDirectory->delete();

        return $validationReport;
    }
}