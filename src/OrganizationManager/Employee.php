<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Contracts\Authentication\AccountManagement;
use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;

class Employee implements EmployeeContract
{
    protected $name;
    protected $description;
    protected $organization;
    protected $loginAccount;

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function setName(string $name): void
    {
        // TODO: Implement setName() method.
    }

    public function getDescription(): string
    {
        // TODO: Implement getDescription() method.
    }

    public function setDescription(string $description): void
    {
        // TODO: Implement setDescription() method.
    }

    public function getOrganization(): OrganizationContract
    {
        // TODO: Implement getOrganization() method.
    }

    public function getLoginAccount(): AccountManagement
    {
        // TODO: Implement getLoginAccount() method.
    }

    public static function create(string $name, string $description, AccountManagement $loginAccount, OrganizationContract $organization): EmployeeContract
    {
        // TODO: Implement create() method.
    }
}