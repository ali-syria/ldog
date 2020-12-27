<?php


namespace AliSyria\LDOG\GraphStore;


class ResourceLabel
{
    public string $uri;
    public ?string $label;

    public function __construct(string $uri,?string $label)
    {
        $this->uri=$uri;
        $this->label=$label;
    }
}