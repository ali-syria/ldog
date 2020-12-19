<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Contracts\OrganizationManager\HasParentContract;
use AliSyria\LDOG\Contracts\OrganizationManager\ModellingOrganizationContract;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use Illuminate\Support\Collection;

class Institution extends Organization implements HasParentContract,ModellingOrganizationContract
{
    const LDOG_CLASS='Institution';
    const LDOG_PARENT_PROPERTY='isInstitutionOf';

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