<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Contracts\OrganizationManager\ModellingOrganizationContract;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use Illuminate\Support\Collection;

class Cabinet extends Organization implements ModellingOrganizationContract
{
    const LDOG_CLASS='Cabinet';

    public function cabinetOrganizations()
    {
        return $this->childOrganizations()
            ->filter(fn(Organization $org)=>$org instanceof CabinetOrganization);
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