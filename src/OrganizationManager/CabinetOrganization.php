<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Contracts\OrganizationManager\HasParentContract;
use AliSyria\LDOG\Contracts\OrganizationManager\ModellingOrganizationContract;

abstract class CabinetOrganization extends Organization implements HasParentContract,
    ModellingOrganizationContract
{

}