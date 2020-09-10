<?php


namespace AliSyria\LDOG\Facades;


use Illuminate\Support\Facades\Facade;

class GS extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ldog.gs.manager';
    }
}