<?php


namespace AliSyria\LDOG\OuterLinkage;


use AliSyria\LDOG\Contracts\OuterLinkage\OuterLinkageTargetContract;
use Illuminate\Support\Collection;

class OuterLinkageTarget implements OuterLinkageTargetContract
{

    public function getSparqlEndpoint(): string
    {
        // TODO: Implement getSparqlEndpoint() method.
    }

    public function getTargetClass(): string
    {
        // TODO: Implement getTargetClass() method.
    }

    public function getTargetProperties(): Collection
    {
        // TODO: Implement getTargetProperties() method.
    }
}