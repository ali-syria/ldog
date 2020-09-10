<?php


namespace AliSyria\LDOG\UriBuilder;


use AliSyria\LDOG\Contracts\UriBuilder\DataShapeUriContract;
use AliSyria\LDOG\Contracts\UriBuilder\OntologyUriContract;
use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;

class Factory
{
    private string $domain;

    public function __construct()
    {
        $this->domain=config('ldog.domain');
    }
    public function realResource(string $sector,string $concept,string $reference):RealResourceUriContract
    {
        return new RealResourceUri($this->domain,$sector,$concept,$reference);
    }
    public function ontology(string $sector,string $name):OntologyUriContract
    {
        return new OntologyUri($this->domain,$sector,$name);
    }
    public function dataShape(string $sector,string $name):DataShapeUriContract
    {
        return new DataShapeUri($this->domain,$sector,$name);
    }
}