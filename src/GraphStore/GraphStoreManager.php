<?php


namespace AliSyria\LDOG\GraphStore;


use AliSyria\LDOG\Contracts\GraphStore\ConnectionContract;

class GraphStoreManager
{
    private ConnectionContract $connection;

    public function __construct()
    {
        $this->connection=app('ldog.gs.open');
    }

    public function openConnection():ConnectionContract
    {
        $this->connection= app('ldog.gs.open');

        return $this->connection;
    }
    public function secureConnection():ConnectionContract
    {
        $this->connection= app('ldog.gs.secure');

        return $this->connection;
    }
    public function getConnection():ConnectionContract
    {
        return $this->connection;
    }
}