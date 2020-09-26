<?php


namespace AliSyria\LDOG\Contracts\OrganizationManager;


use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;
use Illuminate\Support\Collection;

interface OrganizationContract
{
    public static function getLdogClass():string;
    public static function generateId(string $name):string ;
    public static function generateUri(string $name,OrganizationContract $parent=null):RealResourceUriContract ;
    public static function checkIfOrganizationExist(string $uri):bool;
    public function getUri():string;
    public function getName():string;
    public function getDescription():string;
    public function getLogoUrl():?string;

    public function parentOrganization():?OrganizationContract;
    public function childOrganizations():?Collection;
    public function employees():Collection;
    public function admin():EmployeeContract;

    public static function create(?OrganizationContract $parentOrganization,string $name,
         string $description,string $logoUrl=null):self;
}