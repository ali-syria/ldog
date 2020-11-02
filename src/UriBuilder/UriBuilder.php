<?php


namespace AliSyria\LDOG\UriBuilder;


use AliSyria\LDOG\Contracts\UriBuilder\UriBuilderContract;

abstract class UriBuilder implements UriBuilderContract
{
    private string $domain;
    private string $subdomain;

    public const PREFIX_RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#";
    public const PREFIX_RDFS="http://www.w3.org/2000/01/rdf-schema#";
    public const PREFIX_OWL="http://www.w3.org/2002/07/owl#";
    public const PREFIX_LDOG="http://ldog.org/ontologies/2020/8/framework#";
    public const PREFIX_CONVERSION="http://ldog.org/ontologies/2020/11/conversion#";
    public const PREFIX_XSD="http://www.w3.org/2001/XMLSchema#";
    public const PREFIX_LUCENE="http://www.ontotext.com/owlim/lucene#";
    public const PREFIX_SHACL="http://www.w3.org/ns/shacl#";
    public const PREFIX_SCHEMA="http://schema.org/";

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