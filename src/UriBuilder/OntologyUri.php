<?php


namespace AliSyria\LDOG\UriBuilder;


use AliSyria\LDOG\Contracts\UriBuilder\OntologyUriContract;

class OntologyUri extends Builder implements OntologyUriContract
{
    private string $name;

    public function __construct(string $domain,string $subdomain,string $name)
    {
        parent::__construct($domain,$subdomain);
        $this->setName($name);
    }
    public function setName(string $name)
    {
        $this->name=$name;
    }
    public function getBasueUri(): string
    {
        return $this->getTopicUri()."/ontology/$this->name#";
    }

    public function getResourceUri(string $resouce): string
    {
        return $this->getBasueUri().$resouce;
    }
}