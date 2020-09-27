<?php


namespace AliSyria\LDOG\Contracts\OrganizationManager;


interface OrganizationFactoryContract
{
    public static function retrieveByUri(string $uri):?OrganizationContract ;
    public static function create(string $ldogClass,string $name,string $description,
                                  ?string $logoUrl):OrganizationContract;
    public static function resolveLdogClassUriToClass(string $uri):string ;
}