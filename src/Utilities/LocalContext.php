<?php


namespace AliSyria\LDOG\Utilities;


use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\OrganizationManager\Employee;
use AliSyria\LDOG\OrganizationManager\Organization;

class LocalContext
{
    public User $user;
    public Employee $employee;
    public Organization $organization;

    private function __construct()
    {
        $this->user=auth()->user();
        $this->employee=Employee::retrieveByLoginAccount($this->user);
        $this->organization=$this->employee->getOrganization();
    }

    public static function make():self
    {
        static $instance=null;
        if(is_null($instance))
        {
            $instance=new self();
        }

        return $instance;
    }
}