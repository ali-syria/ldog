<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use Illuminate\Support\Collection;

class IndependentAgency extends CabinetOrganization
{
    const LDOG_CLASS='IndependentAgency';
    const LDOG_PARENT_PROPERTY='isIndependentAgencyOf';

    public function exportTargets(): Collection
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $allowedExportTargets=[
            $ldogPrefix.DataExporterTarget::MODELLING_ORGANIZATION,
            $ldogPrefix.DataExporterTarget::ALL_DEPARTMENTS,
            $ldogPrefix.DataExporterTarget::ALL_BRANCHES,
            $ldogPrefix.DataExporterTarget::ALL_SECTORS,
        ];
        return DataExporterTarget::all()->whereIn('uri',$allowedExportTargets);
    }
}