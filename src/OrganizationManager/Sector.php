<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Contracts\OrganizationManager\DataSourceOrganizationContract;
use AliSyria\LDOG\Contracts\OrganizationManager\HasParentContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\OrganizationManager\WeakOrganizationContract;
use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;
use AliSyria\LDOG\Facades\URI;
use Illuminate\Support\Collection;

abstract class Sector extends Organization implements HasParentContract,WeakOrganizationContract,
    DataSourceOrganizationContract
{
    public static function generateSubUri(string $name,OrganizationContract $parent):RealResourceUriContract
    {
        return URI::realResource('organizations',$parent::getLdogClass(),
            static::generateId($parent->getName()),static::getLdogClass(),
            static::generateId($name));
    }

    public function exportTargets(): Collection
    {
        return collect([]);
    }
}