<?php


namespace AliSyria\LDOG\TemplateBuilder;


use AliSyria\LDOG\Contracts\OrganizationManager\ModellingOrganizationContract;
use AliSyria\LDOG\Contracts\ShapesManager\DataShapeContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataCollectionTemplateContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;

class DataCollectionTemplate extends DataTemplate implements DataCollectionTemplateContract
{
    public function __construct(string $identifier,string $label, string $description, DataShapeContract $dataShape,
         ModellingOrganizationContract $modellingOrganization, DataExporterTarget $dataExporterTarget,
         DataDomain $dataDomain)
    {
        parent::__construct($identifier,$label, $description, $dataShape, $modellingOrganization, $dataExporterTarget,
            $dataDomain);
    }

    public static function create(string $identifier,string $label, string $description, DataShapeContract $dataShape,
         ModellingOrganizationContract $modellingOrganization, DataExporterTarget $dataExporterTarget,
         DataDomain $dataDomain)
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;
        $templateUri=URI::template($dataDomain->subDomain,$identifier)->getUri();

        $query="
            PREFIX ldog: <$ldogPrefix>
            PREFIX rdfs: <$rdfsPrefix>
            
            INSERT DATA 
            {
                <$templateUri> a ldog:DataCollectionTemplate;
                        rdfs:label '$label' ;
                        rdfs:comment '$description' ;
                        ldog:hasShape <{$dataShape->getUri()}> ; 
                        ldog:isDataCollectionTemplateOf <{$modellingOrganization->getUri()}>;
                        ldog:shouldDataCollectionExportedBy <{$dataExporterTarget->uri}> ;
                        ldog:dataDomain     <{$dataDomain->uri}> .
            }  
        ";
        GS::getConnection()->rawUpdate($query);

        return new self($identifier,$label,$description,$dataShape,$modellingOrganization,
            $dataExporterTarget,$dataDomain);
    }
}