<?php


namespace AliSyria\LDOG\Contracts\Reconciliation;


interface ReconcilatiationTermContract
{
    public function getUri():string ;
    public function getLable():string ;
    public function getScore():float ;
}