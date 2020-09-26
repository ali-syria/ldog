<?php


namespace AliSyria\LDOG\Contracts\OrganizationManager;


use AliSyria\LDOG\Contracts\Authentication\AccountManagement;

interface EmployeeContract
{
    public function getName():string;
    public function setName(string $name):void;
    public function getDescription():string;
    public function setDescription(string $description):void;

    public function getOrganization():OrganizationContract;
    public function getLoginAccount():AccountManagement;

    public static function create(string $name,string $description,
         AccountManagement $loginAccount,OrganizationContract $organization):self;
}