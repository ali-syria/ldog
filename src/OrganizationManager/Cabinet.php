<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Contracts\OrganizationManager\ModellingOrganizationContract;

class Cabinet extends Organization implements ModellingOrganizationContract
{
    const LDOG_CLASS='Cabinet';

    public function cabinetOrganizations()
    {
        return $this->childOrganizations()
            ->filter(fn(Organization $org)=>$org instanceof CabinetOrganization);
    }
}