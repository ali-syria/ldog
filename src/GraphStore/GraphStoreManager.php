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

    public function openConnection():self
    {
        $this->connection= app('ldog.gs.open');

        return $this;
    }
    public function secureConnection():self
    {
        $this->connection= app('ldog.gs.secure');

        return $this;
    }
    public function getConnection():ConnectionContract
    {
        return $this->connection;
    }
}