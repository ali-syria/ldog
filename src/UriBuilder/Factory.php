<?php


namespace AliSyria\LDOG\UriBuilder;


use AliSyria\LDOG\Contracts\UriBuilder\DataShapeUriContract;
use AliSyria\LDOG\Contracts\UriBuilder\DataTemplateUriContract;
use AliSyria\LDOG\Contracts\UriBuilder\OntologyUriContract;
use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;
use AliSyria\LDOG\Contracts\UriBuilder\UriExistenceCheckerContract;
use AliSyria\LDOG\Facades\GS;

class Factory implements UriExistenceCheckerContract
{
    private string $domain;

    public function __construct()
    {
        $this->domain=config('ldog.domain');
    }
    public function realResource(string $sector,string $concept,string $reference,
           string $subConcept=null,string $subReference=null):RealResourceUriContract
    {
        return new RealResourceUri($this->domain,$sector,$concept,$reference,
            $subConcept,$subReference);
    }
    public function ontology(string $sector,string $name):OntologyUriContract
    {
        return new OntologyUri($this->domain,$sector,$name);
    }
    public function dataShape(string $sector,string $name):DataShapeUriContract
    {
        return new DataShapeUri($this->domain,$sector,$name);
    }
    public function template(string $sector,string $name):DataTemplateUriContract
    {
        return new DataTemplateUri($this->domain,$sector,$name);
    }
    public function isUriExist($uri): bool
    {
        return GS::getConnection()->isResourceExist($uri);
    }
}