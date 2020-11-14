<?php


namespace AliSyria\LDOG\Facades;


use Illuminate\Support\Facades\Facade;

class VAL extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ldog.validator';
    }
}