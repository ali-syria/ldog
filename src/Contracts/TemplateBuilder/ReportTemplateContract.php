<?php


namespace AliSyria\LDOG\Contracts\TemplateBuilder;


use AliSyria\LDOG\Contracts\OrganizationManager\ModellingOrganizationContract;
use AliSyria\LDOG\Contracts\ShapesManager\DataShapeContract;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use AliSyria\LDOG\Utilities\LdogTypes\ReportExportFrequency;

interface ReportTemplateContract
{
    public static function create(string $identifier,string $label,string $description,DataShapeContract $dataShape,
        ModellingOrganizationContract $modellingOrganization,DataExporterTarget $dataExporterTarget,
        DataDomain $dataDomain,ReportExportFrequency $exportFrequency,string $silkLslSpecs=null);
}