<?php


namespace AliSyria\LDOG\BatchImporter;


use AliSyria\LDOG\Contracts\BatchImporter\BatchImport;
use AliSyria\LDOG\Contracts\BatchImporter\DataCollectionContract;
use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Carbon\Carbon;

class DataCollection extends BatchImport implements DataCollectionContract
{
    public static function create(string $conversionId,string $conversionPath,string $datasetPath,string $label,
                                  ?string $description,DataTemplate $dataTemplate,OrganizationContract $organization,
                                  EmployeeContract $employee,Carbon $fromDate=null,Carbon $toDate=null)
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;
        $templateUri=URI::template($dataDomain->subDomain,$identifier)->getUri();

        $query="
            PREFIX ldog: <$ldogPrefix>
            PREFIX rdfs: <$rdfsPrefix>
            
            INSERT DATA 
            {
                <$templateUri> a ldog:ReportTemplate ;
                        rdfs:label '$label' ;
                        rdfs:comment '$description' ;
                        ldog:hasShape <{$dataShape->getUri()}> ; 
                        ldog:isDataCollectionTemplateOf <{$modellingOrganization->getUri()}>;
                        ldog:shouldDataCollectionExportedBy <{$dataExporterTarget->uri}> ;
                        ldog:dataDomain     <{$dataDomain->uri}> ;
                        ldog:frequencyOfExport <{$exportFrequency->uri}> .
            }  
        ";
        GS::getConnection()->rawUpdate($query);

        return new self($templateUri,$label,$description,$dataShape,$modellingOrganization,
            $dataExporterTarget,$dataDomain,$exportFrequency);
    }

    public static function retrieve(string $uri): ?self
    {

    }
}