<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Contracts\OrganizationManager\HasParentContract;
use AliSyria\LDOG\Contracts\OrganizationManager\ModellingOrganizationContract;

class Institution extends Organization implements HasParentContract,ModellingOrganizationContract
{
    const LDOG_CLASS='Institution';
    const LDOG_PARENT_PROPERTY='isInstitutionOf';
}