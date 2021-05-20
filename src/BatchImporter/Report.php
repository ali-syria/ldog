<?php


namespace AliSyria\LDOG\BatchImporter;


use AliSyria\LDOG\Contracts\BatchImporter\BatchImport;
use AliSyria\LDOG\Contracts\BatchImporter\ReportContract;
use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OrganizationManager\Employee;
use AliSyria\LDOG\OrganizationManager\OrganizationFactory;
use AliSyria\LDOG\PublishingPipeline\PublishingPipeline;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\TemplateBuilder\ReportTemplate;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\ReportExportFrequency;
use Carbon\Carbon;

class Report extends BatchImport implements ReportContract
{

    public static function create(string $conversionId,string $conversionPath,string $datasetPath,string $label,
                                  ?string $description,DataTemplate $dataTemplate,OrganizationContract $organization,
                                  EmployeeContract $employee,Carbon $fromDate=null,Carbon $toDate=null)
    {
        $conversionUri=PublishingPipeline::getConversionUri($conversionId);
        $batchImportUri=self::getBatchImportUri($conversionId);

        GS::getConnection()
            ->loadIRIintoNamedGraph(UriBuilder::convertRelativeFilePathToUrl($conversionPath),$conversionUri);
        GS::getConnection()
            ->loadIRIintoNamedGraph(UriBuilder::convertRelativeFilePathToUrl($datasetPath),$batchImportUri);

        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $conversionPrefix=UriBuilder::PREFIX_CONVERSION;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;
        $fromDateStr=(string) $fromDate;
        $toDateStr=(string) $toDate;
        if($dataTemplate->exportFrequency->uri==UriBuilder::PREFIX_LDOG.ReportExportFrequency::YEARLY)
        {
            $fromDateStr=(string) $fromDate->year;
            $toDateStr=(string) $fromDate->year;
        }
        $query="
            PREFIX ldog: <$ldogPrefix>
            PREFIX conv: <$conversionPrefix>
            PREFIX rdfs: <$rdfsPrefix>

            INSERT DATA
            {
                <$batchImportUri> a ldog:Report ;
                        rdfs:label '$label' ;
                        rdfs:comment '$description' ;
                        ldog:basedOnTemplate <{$dataTemplate->uri}> ;
                        conv:basedOnConversion <{$conversionUri}> ;
                        ldog:publisher <{$organization->getUri()}> ;
                        ldog:publishedBy <{$employee->getUri()}> ;
                        ldog:fromDate '$fromDateStr' ;
                        ldog:toDate '$toDateStr' .
            }
        ";
        GS::getConnection()->rawUpdate($query);

        return new self($batchImportUri,$label,$description,$dataTemplate,$conversionUri,
            $organization,$employee,$fromDate->setMicro(0),$toDate->setMicro(0));
    }

    public static function retrieve(string $uri): ?self
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $conversionPrefix=UriBuilder::PREFIX_CONVERSION;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;

        $resultSet=GS::secureConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            PREFIX conv: <$conversionPrefix>
            PREFIX rdfs: <$rdfsPrefix>
            
            SELECT ?label ?description ?dataTemplate ?dataTemplateType ?conversion ?organization ?employee ?fromDate ?toDate
            WHERE {
                  <$uri>  a ldog:Report ;
                        rdfs:label ?label ;                        
                        ldog:basedOnTemplate ?dataTemplate ;
                        conv:basedOnConversion ?conversion ;
                        ldog:publisher ?organization ;
                        ldog:publishedBy ?employee ;
                  OPTIONAL {
                    <$uri> rdfs:comment ?description ;
                        ldog:fromDate ?fromDate ;
                        ldog:toDate ?toDate .    
                    ?dataTemplate a  ?dataTemplateType .                                       
                  }                     
            }                                       
        ");

        $report=null;
        foreach ($resultSet as $result)
        {
            $organization=OrganizationFactory::retrieveByUri($result->organization->getUri());
            $employee=Employee::retrieveByUri($result->employee->getUri());
            $dataTemplateTypeUri=$result->dataTemplateType->getUri();
            $dataTemplate=null;

            if ($dataTemplateTypeUri==$ldogPrefix.'ReportTemplate')
            {
                $dataTemplate=ReportTemplate::retrieve($result->dataTemplate->getUri());
            }

            $fromDate=optional($result->fromDate)->getValue();
            $toDate=optional($result->toDate)->getValue();
            $fromDate=$fromDate? Carbon::parse($fromDate) : null;
            $toDate=$toDate? Carbon::parse($toDate) : null;
            $description=optional($result->description)->getValue();
            $description=filled($description) ? :null;

            $report= new self($uri,$result->label->getValue(),$description,
                $dataTemplate,$result->conversion->getUri(),$organization,$employee,
                $fromDate,$toDate);
            break;
        }

        return $report;
    }
}
