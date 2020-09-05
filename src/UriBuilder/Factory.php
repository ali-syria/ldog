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
    public function realResource(string $topic,string $concept,string $reference):RealResourceUriContract
    {
        return new RealResourceUri($this->domain,$topic,$concept,$reference);
    }
    public function ontology(string $topic,string $name):OntologyUriContract
    {
        return new OntologyUri($this->domain,$topic,$name);
    }
    public function dataShape(string $topic,string $name):DataShapeUriContract
    {
        return new DataShapeUri($this->domain,$topic,$name);
    }
}