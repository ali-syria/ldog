<?php


namespace AliSyria\LDOG\Contracts\OrganizationManager;


use AliSyria\LDOG\Contracts\Authentication\AccountManagement;

interface EmployeeContract
{
    public function getId():string;
    public function getUri():string;
    public function getName():string;
    public function getDescription():string;

    public function getOrganization():OrganizationContract;
    public function getLoginAccount():AccountManagement;

    public static function retrieveByLoginAccount(AccountManagement $loginAccount):?self;
    public static function create(OrganizationContract $organization,AccountManagement $loginAccount,
                                  string $id,string $name,string $description):self;
}