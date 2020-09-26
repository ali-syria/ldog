<?php


namespace AliSyria\LDOG\Contracts\OrganizationManager;


use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;

interface WeakOrganizationContract
{
    public static function generateSubUri(string $name,OrganizationContract $parent):RealResourceUriContract;
}