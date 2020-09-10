<?php


namespace AliSyria\LDOG\GraphStore;


use AliSyria\LDOG\Contracts\GraphStore\ConnectionContract;

class ConnectionFactory
{
    public static function make(string $key):ConnectionContract
    {
        $config=config('ldog.graph_stores.'.$key);

        return app($config['driver'])->connect($key);
    }
}