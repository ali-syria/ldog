<?php


namespace AliSyria\LDOG\UriBuilder;


use AliSyria\LDOG\Contracts\UriBuilder\DataShapeUriContract;
use Illuminate\Support\Str;

class DataShapeUri extends UriBuilder implements DataShapeUriContract
{

    private string $name;

    public function __construct(string $domain,string $subdomain,string $name)
    {
        parent::__construct($domain,$subdomain);
        $this->setName($name);
    }
    public function setName(string $name)
    {
        $this->name=Str::of($name)->trim(' ')->trim()->kebab()->slug();
    }
    public function getBasueUri(): string
    {
        return $this->getSectorUri()."/shape/$this->name";
    }

    public function getResourceUri(string $resouce): string
    {
        return $this->getBasueUri().Str::of($resouce)->trim()->slug();
    }
}