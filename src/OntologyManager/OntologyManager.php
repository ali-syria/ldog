<?php


namespace AliSyria\LDOG\OntologyManager;


use AliSyria\LDOG\Contracts\OntologyManager\OntologyImporterContract;
use AliSyria\LDOG\Exceptions\OntologyAlreadyExist;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\UriBuilder\UriBuilder;

class OntologyManager implements OntologyImporterContract
{

    public static function importFromUrl(string $url, string $sector, string $prefix, string $namespace):void
    {
        $ontlogyUri=self::generateUri($sector,$prefix);

        throw_if(self::checkIfExist($ontlogyUri),
            new OntologyAlreadyExist('an ontology with same prefix already exists'));

        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        GS::getConnection()->loadIRIintoNamedGraph($url,$ontlogyUri);

        GS::getConnection()->rawUpdate("
            PREFIX ldog: <$ldogPrefix> 
            
            INSERT DATA {
                GRAPH <$ontlogyUri> {
                    <$namespace> a ldog:Ontology ;
                                 ldog:prefix '$prefix' .
                }
            }
        ");
    }
    public static function generateUri(string $sector, string $prefix):string
    {
        return URI::ontology($sector,$prefix)->getBasueUri();
    }
    public static function checkIfExist(string $ontologyUri):bool
    {
        return GS::getConnection()->isGraphExist($ontologyUri);
    }
}