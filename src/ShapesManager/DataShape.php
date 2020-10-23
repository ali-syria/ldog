<?php


namespace AliSyria\LDOG\ShapesManager;


use AliSyria\LDOG\Contracts\ShapesManager\DataShapeContract;

class DataShape implements DataShapeContract
{
    protected string $uri;

    public function __construct(string $uri)
    {
        $this->uri=$uri;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}