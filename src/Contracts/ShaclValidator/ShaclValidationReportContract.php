<?php


namespace AliSyria\LDOG\Contracts\ShaclValidator;


use Illuminate\Support\Collection;

interface ShaclValidationReportContract
{
    public function isConforms():bool ;
    public function results():Collection;
}