<?php


namespace AliSyria\LDOG\Contracts\UriBuilder;


interface RealResourceUriContract
{
    public function getResourceUri():string;
    public function getHtmlUri():string;
    public function getDataUri():string;
}