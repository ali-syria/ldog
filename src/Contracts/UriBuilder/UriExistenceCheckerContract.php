<?php


namespace AliSyria\LDOG\Contracts\UriBuilder;


interface UriExistenceCheckerContract
{
    public function isUriExist(string $uri):bool;
}