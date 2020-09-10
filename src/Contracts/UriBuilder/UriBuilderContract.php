<?php


namespace AliSyria\LDOG\Contracts\UriBuilder;


interface UriBuilderContract
{
    public function getTopLevelDomain():string;
    public function getSubDomain():string;
    public function getSectorUri():string ;
}