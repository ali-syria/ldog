<?php


namespace AliSyria\LDOG\Contracts\TemplateBuilder;


use AliSyria\LDOG\Contracts\OrganizationManager\ModellingOrganizationContract;
use AliSyria\LDOG\Contracts\ShapesManager\DataShapeContract;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;

abstract class DataTemplate
{
    public string $identifier;
    public string $label;
    public string $description;
    public DataShapeContract $dataShape;
    public ModellingOrganizationContract $modellingOrganization;
    public DataExporterTarget $dataExporterTarget;
    public DataDomain $dataDomain;

    public function __construct(string $identifier,string $label,string $description,DataShapeContract $dataShape,
        ModellingOrganizationContract $modellingOrganization,DataExporterTarget $dataExporterTarget,
        DataDomain $dataDomain)
    {
        $this->identifier=$identifier;
        $this->label=$label;
        $this->description=$description;
        $this->dataShape=$dataShape;
        $this->modellingOrganization=$modellingOrganization;
        $this->dataExporterTarget=$dataExporterTarget;
        $this->dataDomain=$dataDomain;
    }
}