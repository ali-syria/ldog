<?php


namespace AliSyria\LDOG\UriBuilder;


use AliSyria\LDOG\Contracts\UriBuilder\UriBuilderContract;

abstract class UriBuilder implements UriBuilderContract
{
    private string $domain;
    private string $subdomain;

    public const PREFIX_LDOG="http://ldog.org/ontologies/2020/8/framework#";
    public const PREFIX_XSD="http://www.w3.org/2001/XMLSchema#";

    public function __construct(string $domain,string $subdomain)
    {
        $this->domain= $domain;
        $this->subdomain=$subdomain;
    }

    public function getTopLevelDomain(): string
    {
        return $this->domain;
    }
    public function getSubDomain(): string
    {
        return $this->subdomain;
    }
    public final function getSectorUri():string
    {
        return "http://".$this->subdomain.".".$this->domain;
    }
}