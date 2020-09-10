<?php


namespace AliSyria\LDOG\Contracts\GraphStore;


interface ConnectionContract
{
    public function connect(string $connectionConfigKey);
    public function getHost():string;
    public function getRepository():string;
    public function getUsername():string;
    public function getPassword():string;
}