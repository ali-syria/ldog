<?php


namespace AliSyria\LDOG\Contracts\UriBuilder;


interface OntologyUriContract
{
    public function getBasueUri():string ;
    public function getResourceUri(string $resouce):string ;
}