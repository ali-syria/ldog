<?php


namespace AliSyria\LDOG\OntologyManager;


use AliSyria\LDOG\Contracts\OntologyManager\OntologyImporterContract;
use AliSyria\LDOG\Exceptions\OntologyAlreadyExist;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use Illuminate\Support\Collection;

class OntologyManager implements OntologyImporterContract
{

    public static function importFromUrl(string $url,DataDomain $dataDomain, string $prefix,
                                         string $namespace,string $description):void
    {
        $ontlogyUri=self::generateUri($dataDomain->subDomain,$prefix);

        throw_if(self::checkIfExist($ontlogyUri),
            new OntologyAlreadyExist('an ontology with same prefix already exists'));

        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        GS::getConnection()->loadIRIintoNamedGraph($url,$ontlogyUri);

        GS::getConnection()->rawUpdate("
            PREFIX ldog: <$ldogPrefix> 
            
            INSERT DATA {
                    <$ontlogyUri> a ldog:Ontology ;
                                 ldog:prefix '$prefix' ;
                                 ldog:namespace '$namespace' ;
                                 ldog:description '$description' ;
                                 ldog:dataDomain     <{$dataDomain->uri}> .
            }
        ");
    }
    public static function importLdogOntology()
    {
        $ldogFileUrl=UriBuilder::convertRelativeFilePathToUrl(__DIR__.'/../../ontologies/ldog.ttl');
        GS::getConnection()
            ->loadIRIintoNamedGraph($ldogFileUrl,'http://ldog.com/ontology');
    }
    public static function importConversionOntology()
    {
        $conversionPath=UriBuilder::convertRelativeFilePathToUrl(__DIR__.'/../../ontologies/conversion.ttl');
        GS::getConnection()
            ->loadIRIintoNamedGraph($conversionPath,'http://ldog.com/ontology/conversion');
    }
    public static function generateUri(string $sector, string $prefix):string
    {
        return URI::ontology($sector,$prefix)->getBasueUri();
    }
    public static function checkIfExist(string $ontologyUri):bool
    {
        return GS::getConnection()->isGraphExist($ontologyUri);
    }
    public static function getAll():Collection
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $query="
            PREFIX ldog: <$ldogPrefix> 
            
            SELECT ?ontlogy ?prefix ?namespace ?description ?dataDomain
            WHERE {
                    ?ontlogy a ldog:Ontology ;
                     ldog:prefix ?prefix ;
                     ldog:namespace ?namespace ;
                     ldog:description ?description ;
                     ldog:dataDomain  ?dataDomain .
            }
        ";
        $resultSet=GS::openConnection()->jsonQuery($query);

        $ontologies=[];
        foreach ($resultSet as $result)
        {
            if(optional($result)->ontlogy)
            {
                $ontologies[]= new Ontology($result->ontlogy->getUri(),$result->prefix->getValue(),
                    $result->namespace->getValue(),$result->description->getValue(),
                    DataDomain::find($result->dataDomain->getUri()));
            }
        }

        return collect($ontologies);
    }
}