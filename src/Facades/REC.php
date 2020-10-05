<?php


namespace AliSyria\LDOG\Facades;


use Illuminate\Support\Facades\Facade;

class REC extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ldog.reconciliation';
    }
}