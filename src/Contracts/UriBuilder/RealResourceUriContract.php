<?php


namespace AliSyria\LDOG\Contracts\UriBuilder;


interface RealResourceUriContract
{
    public function getResourcePath():string;
    public function getHtmlPath():string;
    public function getDataPath():string;
    public function getResourceUri():string;
    public function getHtmlUri():string;
    public function getDataUri():string;
}