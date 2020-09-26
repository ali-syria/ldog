<?php


namespace AliSyria\LDOG\UriBuilder;


use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;
use Illuminate\Support\Str;

class RealResourceUri extends UriBuilder implements RealResourceUriContract
{
    private string $concept;
    private string $reference;
    private string $subConcept;
    private string $subReference;

    public function __construct(string $domain,string $subdomain,string $concept,string $reference,
                                string $subConcept=null,string $subReference=null)
    {
        parent::__construct($domain,$subdomain);
        $this->setConcept($concept);
        $this->setReference($reference);
        if(!is_null($subConcept))
        {
            $this->setSubConcept($subConcept);
            $this->setSubReference($subReference);
        }
    }

    public function setConcept(string $concept)
    {
        $this->concept=Str::of($concept)->kebab();
    }
    public function setReference(string $reference)
    {
        $this->reference=$reference;
    }
    public function setSubConcept(string $subConcept)
    {
        $this->subConcept=Str::of($subConcept)->kebab();
    }
    public function setSubReference(string $subReference)
    {
        $this->subReference=$subReference;
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

    public function getSubResourcePath(): string
    {
        return $this->getResourcePath()."/$this->subConcept/$this->subReference";
    }

    public function getSubHtmlPath(): string
    {
        return $this->getHtmlPath()."/$this->subConcept/$this->subReference";
    }

    public function getSubDataPath(): string
    {
        return $this->getDataPath()."/$this->subConcept/$this->subReference";
    }

    public function getSubResourceUri(): string
    {
        return $this->getSectorUri().'/'.$this->getSubResourcePath();
    }

    public function getSubHtmlUri(): string
    {
        return $this->getSectorUri().'/'.$this->getSubHtmlPath();
    }

    public function getSubDataUri(): string
    {
        return $this->getSectorUri().'/'.$this->getSubDataPath();
    }
}