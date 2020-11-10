<?php


namespace AliSyria\LDOG\Contracts\PublishingPipeline;


use AliSyria\LDOG\Contracts\ShaclValidator\ShaclValidationReportContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use Illuminate\Support\Collection;

interface PublishingPipelineContract
{
    public static function initiate(DataTemplate $dataTemplate,string $csvPath):self;
    public static function make(string $conversionUuid):self;

    public function generateRawRdf(array $mappings):void ;
    public function normalize():void ;
    public function reconcile(Collection $termResourceMappings):void ;
    public function validate():ShaclValidationReportContract;
    public function publish():void ;
    public function linkToOthersDatasets():void ;
}