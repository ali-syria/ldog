<?php


namespace AliSyria\LDOG\BatchImporter;


use AliSyria\LDOG\Contracts\BatchImporter\BatchImport;
use AliSyria\LDOG\Contracts\BatchImporter\ReportContract;
use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use Carbon\Carbon;

class Report extends BatchImport implements ReportContract
{

    public static function create(string $conversionId,string $conversionPath,string $datasetPath,string $label,
                                  ?string $description,DataTemplate $dataTemplate,OrganizationContract $organization,
                                  EmployeeContract $employee,Carbon $fromDate=null,Carbon $toDate=null)
    {
        // TODO: Implement create() method.
    }

    public static function retrieve(string $uri): ?self
    {
        // TODO: Implement retrieve() method.
    }
}