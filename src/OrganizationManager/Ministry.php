<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use Illuminate\Support\Collection;

class Ministry extends CabinetOrganization
{
    const LDOG_CLASS='Ministry';
    const LDOG_PARENT_PROPERTY='isMinistryOf';

    public function instituations()
    {
        return $this->childOrganizations()
            ->filter(fn(Organization $org)=>$org instanceof Institution);
    }

    public function exportTargets(): Collection
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $allowedExportTargets=[
            $ldogPrefix.DataExporterTarget::MODELLING_ORGANIZATION,
            $ldogPrefix.DataExporterTarget::ALL_DEPARTMENTS,
        ];
        return DataExporterTarget::all()->whereIn('uri',$allowedExportTargets);
    }
}