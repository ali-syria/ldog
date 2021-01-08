<?php


namespace AliSyria\LDOG\Contracts\PublishingPipeline;

use Carbon\Carbon;
use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\ShaclValidator\ShaclValidationReportContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use Illuminate\Support\Collection;
use ML\JsonLD\Node;

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
    public function updateIndex():void;

    public function updateObjectValue(Node $resource,string $predicateUri,$oldTerm,$newTerm,bool $save=true):void;
    public function bulkUpdateObjectValues(string $predicateUri,$oldTerm,$newTerm):void;
}