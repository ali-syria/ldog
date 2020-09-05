<?php


namespace AliSyria\LDOG\Facades;


use Illuminate\Support\Facades\Facade;

class URI extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ldog.uri';
    }
}