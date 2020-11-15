<?php


namespace AliSyria\LDOG\Contracts\BatchImporter;


use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use Carbon\Carbon;

interface ReportContract
{
    public static function create(string $conversionId,string $conversionPath,string $datasetPath,string $label,
          ?string $description,DataTemplate $dataTemplate,OrganizationContract $organization,
          EmployeeContract $employee,Carbon $fromDate=null,Carbon $toDate=null);
}