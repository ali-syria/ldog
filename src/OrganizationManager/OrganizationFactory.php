<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;

class OrganizationFactory
{
    public function create(string $ldogClass):OrganizationContract
    {
        switch ($ldogClass)
        {
            case Cabinet::LDOG_CLASS:

        }
    }
}