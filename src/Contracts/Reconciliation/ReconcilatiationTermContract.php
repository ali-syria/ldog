<?php


namespace AliSyria\LDOG\Contracts\Reconciliation;


interface ReconcilatiationTermContract
{
    public function getResourceUri():string ;
    public function getLable():string ;
    public function getScore():float ;
}