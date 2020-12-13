<?php


namespace AliSyria\LDOG\UriBuilder;


use AliSyria\LDOG\Contracts\UriBuilder\DataTemplateUriContract;
use Illuminate\Support\Str;

class DataTemplateUri extends UriBuilder implements DataTemplateUriContract
{
    private string $name;

    public function __construct(string $domain,string $subdomain,string $name)
    {
        parent::__construct($domain,$subdomain);
        $this->setName($name);
    }
    public function setName(string $name)
    {
        $this->name=(string)Str::of($name)->trim(' ')->trim()->camel();
    }
    public function getUri(): string
    {
        return $this->getSectorUri()."/template/$this->name";
    }
}