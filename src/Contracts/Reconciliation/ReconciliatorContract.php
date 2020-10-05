<?php


namespace AliSyria\LDOG\Contracts\Reconciliation;


use Illuminate\Support\Collection;

interface ReconciliatorContract
{
    public function getInitCommand(): string;
    public function getIndexName(): string ;
    public function reconcile(string $subjectUri,string $predicateUri,string $literal,
                              string $targetUri,string $graphIRI=null): void;
    public function match(string $literal,string $classUri):?ReconcilatiationTermContract;
    public function search(string $literal,string $classUri):Collection;
}