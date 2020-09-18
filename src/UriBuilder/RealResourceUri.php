<?php


namespace AliSyria\LDOG\UriBuilder;


use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;
use Illuminate\Support\Str;

class RealResourceUri extends Builder implements RealResourceUriContract
{
    private string $concept;
    private string $reference;

    public function __construct(string $domain,string $subdomain,string $concept,string $reference)
    {
        parent::__construct($domain,$subdomain);
        $this->setConcept($concept);
        $this->setReference($reference);
    }

    public function setConcept(string $concept)
    {
        $this->concept=Str::of($concept)->kebab();
    }
    public function setReference(string $reference)
    {
        $this->reference=$reference;
    }

    public function getResourcePath(): string
    {
        return 'resoucre/'.$this->concept.'/'.$this->reference;
    }

    public function getHtmlPath(): string
    {
        return 'page/'.$this->concept.'/'.$this->reference;
    }

    public function getDataPath(): string
    {
        return 'data/'.$this->concept.'/'.$this->reference;
    }

    public function getResourceUri(): string
    {
        return $this->getSectorUri().'/'.$this->getResourcePath();
    }

    public function getHtmlUri(): string
    {
        return $this->getSectorUri().'/'.$this->getHtmlPath();
    }

    public function getDataUri(): string
    {
        return $this->getSectorUri().'/'.$this->getDataPath();
    }
}