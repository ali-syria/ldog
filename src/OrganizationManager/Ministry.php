<?php


namespace AliSyria\LDOG\OrganizationManager;


class Ministry extends CabinetOrganization
{
    const LDOG_CLASS='Ministry';
    const LDOG_PARENT_PROPERTY='isMinistryOf';

    public function instituations()
    {
        return $this->childOrganizations()
            ->filter(fn(Organization $org)=>$org instanceof Institution);
    }
}