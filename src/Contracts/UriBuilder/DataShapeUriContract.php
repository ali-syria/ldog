<?php


namespace AliSyria\LDOG\Contracts\UriBuilder;


interface DataShapeUriContract
{
    public function getBasueUri():string ;
    public function getResourceUri(string $resouce):string ;
}