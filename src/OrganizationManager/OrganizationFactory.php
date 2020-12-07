<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationFactoryContract;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\UriBuilder\UriBuilder;

class OrganizationFactory implements OrganizationFactoryContract
{
    public static function create(string $ldogClass,string $name,string $description,
                           ?string $logoUrl):OrganizationContract
    {

    }
    public static function retrieveByUri(string $uri):?OrganizationContract
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $resultSet=GS::openConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            
            SELECT ?class ?name ?description ?logo
            WHERE {
                  <$uri>  a ?class ;
                          ldog:name ?name ;
                          ldog:description ?description .
                  OPTIONAL {<$uri> ldog:logo ?logo  . }                         
            }                                       
        ",false);
        $organization=null;
        foreach ($resultSet as $result)
        {
            if(optional($result)->class)
            {dd($result->class->getUri());
                $class=self::resolveLdogClassUriToClass($result->class->getUri());
                $organization= new $class($uri,$result->name->getValue(),$result->description->getValue(),
                    optional(optional($result)->logo)->getValue());
            }
            break;
        }

        return $organization;
    }

    public static function resolveLdogClassUriToClass(string $uri): string
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        switch ($uri)
        {
            case $ldogPrefix."Cabinet":
                return Cabinet::class;
            case $ldogPrefix."Ministry":
                return Ministry::class;
            case $ldogPrefix."IndependentAgency":
                return IndependentAgency::class;
            case $ldogPrefix."Institution":
                return Institution::class;
            case $ldogPrefix."Department":
                return Department::class;
            case $ldogPrefix."Branch":
                return Branch::class;
            default:
                throw new \RuntimeException('organization class is not resolved');
        }
    }
}