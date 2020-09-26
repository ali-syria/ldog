<?php


namespace AliSyria\LDOG\Contracts\OrganizationManager;


use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;

interface HasParentContract
{
    public static function getLdogParentProperty():string;
}