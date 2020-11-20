<?php


namespace AliSyria\LDOG\Contracts\OuterLinkage;


interface TargetLinkagePropertyContract
{
    public function getPropertyClass():string ;
    public function getTargetLangs():?array ;
}