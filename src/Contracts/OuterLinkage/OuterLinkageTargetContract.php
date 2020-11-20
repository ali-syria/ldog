<?php


namespace AliSyria\LDOG\Contracts\OuterLinkage;


use Illuminate\Support\Collection;

interface OuterLinkageTargetContract
{
    public function getSparqlEndpoint():string ;
    public function getTargetClass():string ;
    public function getTargetProperties():Collection;
}