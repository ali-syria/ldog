<?php


namespace AliSyria\LDOG\Contracts\PublishingPipeline;


use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\ShaclValidator\ShaclValidationReportContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface PublishingPipelineContract
{
    public static function initiate(DataTemplate $dataTemplate,string $csvPath):self;
    public static function make(string $conversionUuid):self;

    public function generateRawRdf(array $mappings):void ;
    public function normalize():void ;
    public function reconcile(Collection $termResourceMappings):void ;
    public function validate():ShaclValidationReportContract;
    public function publish(OrganizationContract $organization,EmployeeContract $employee,
                            Carbon $fromDate=null,Carbon $toDate=null):void ;
    public function linkToOthersDatasets():void ;
}