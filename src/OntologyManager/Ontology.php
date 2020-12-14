<?php


namespace AliSyria\LDOG\OntologyManager;


use AliSyria\LDOG\Contracts\OntologyManager\OntologyContract;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;

class Ontology implements OntologyContract
{
    public string $uri;
    public string $prefix;
    public string $namespace;
    public string $description;
    public DataDomain $dataDomain;

    public function __construct(string $uri,string $prefix,string $namespace,string $description,
                                DataDomain $dataDomain)
    {
        $this->uri=$uri;
        $this->prefix=$prefix;
        $this->namespace=$namespace;
        $this->description=$description;
        $this->dataDomain=$dataDomain;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDataDomain(): DataDomain
    {
        return $this->dataDomain;
    }
}