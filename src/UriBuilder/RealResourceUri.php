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
        $this->concept=Str::of($concept)->lower()->plural();
    }
    public function setReference(string $reference)
    {
        $this->reference=$reference;
    }

    public function getResourceUri(): string
    {
        return $this->getTopicUri().'/resoucre/'.$this->concept.'/'.$this->reference;
    }

    public function getHtmlUri(): string
    {
        return $this->getTopicUri().'/page/'.$this->concept.'/'.$this->reference;
    }

    public function getDataUri(): string
    {
        return $this->getTopicUri().'/data/'.$this->concept.'/'.$this->reference;
    }
}