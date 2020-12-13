<?php


namespace AliSyria\LDOG\UriBuilder;


use AliSyria\LDOG\Contracts\UriBuilder\OntologyUriContract;
use Illuminate\Support\Str;

class OntologyUri extends UriBuilder implements OntologyUriContract
{
    private string $name;

    public function __construct(string $domain,string $subdomain,string $name)
    {
        parent::__construct($domain,$subdomain);
        $this->setName($name);
    }
    public function setName(string $name)
    {
        $this->name=Str::of($name)->trim();
    }
    public function getBasueUri(): string
    {
        return $this->getSectorUri()."/ontology/$this->name";
    }

    public function getResourceUri(string $resouce): string
    {
        return $this->getBasueUri().Str::of($resouce)->trim();
    }
}