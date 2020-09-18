<?php


namespace AliSyria\LDOG\Contracts\GraphStore;


interface ResourceDescriptionContract
{
    public function getMimeType():string;
    public function getBody():string;
}
