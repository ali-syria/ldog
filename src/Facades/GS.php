<?php


namespace AliSyria\LDOG\Facades;


use AliSyria\LDOG\GraphStore\GraphStoreManager;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin  GraphStoreManager
 */
class GS extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ldog.gs.manager';
    }
}