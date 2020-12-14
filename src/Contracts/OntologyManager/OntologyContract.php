<?php


namespace AliSyria\LDOG\Contracts\OntologyManager;


use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;

interface OntologyContract
{
    public function getUri():string;
    public function getPrefix():string;
    public function getNamespace():string;
    public function getDescription():string;
    public function getDataDomain():DataDomain;
}