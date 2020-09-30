<?php


namespace AliSyria\LDOG\Contracts\GraphStore;


use EasyRdf\Sparql\Result;

interface QueryContract
{
    public function rawQuery(string $query):string;
    public function jsonQuery(string $query):Result;
    public function rdfQuery(string $query):array;
    public function describeResource(string $uri,string $mimeType):ResourceDescriptionContract;
    public function isResourceExist(string $uri):bool;
    public function isGraphExist(string $graphUri):bool;
}