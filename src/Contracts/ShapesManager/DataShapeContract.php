<?php


namespace AliSyria\LDOG\Contracts\ShapesManager;


use Illuminate\Support\Collection;

interface DataShapeContract
{
    public function getUri():string;
    public function getOuterLinkageTargets():Collection;
}