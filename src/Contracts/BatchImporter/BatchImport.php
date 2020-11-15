<?php


namespace AliSyria\LDOG\Contracts\BatchImporter;


use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use Carbon\Carbon;

abstract class BatchImport
{
    public string $uri;
    public string $label;
    public ?string $description;
    public DataTemplate $dataTemplate;
    public string $conversionUri;
    public OrganizationContract $organization;
    public EmployeeContract $employee;
    public ?Carbon $fromDate=null;
    public ?Carbon $toDate=null;

    public function __construct(string $uri,string $label,?string $description,DataTemplate $dataTemplate,
         string $conversionUri,OrganizationContract $organization,EmployeeContract $employee,
         Carbon $fromDate=null,Carbon $toDate=null)
    {
        $this->uri=$uri;
        $this->label=$label;
        $this->description=$description;
        $this->dataTemplate=$dataTemplate;
        $this->conversionUri=$conversionUri;
        $this->organization=$organization;
        $this->employee=$employee;
        $this->fromDate=$fromDate;
        $this->toDate=$toDate;
    }

    abstract public static function retrieve(string $uri):?self ;
}