<?php


namespace AliSyria\LDOG\Contracts\GraphStore;


interface GraphUpdateContract
{
    public function loadIRIintoNamedGraph(string $absolutePath,string $graphIRI);
    public function clearAll();
    public function clearNamedGraph(string $graphIRI);
    public function rawUpdate(string $query):string;
}