<?php


namespace AliSyria\LDOG\Console;


use AliSyria\LDOG\GraphStore\GraphDbDriver;
use Illuminate\Console\Command;

class InitGraphDbLuceneReconciliator extends Command
{
    public $signature= 'ldog:init-graphdb-lucene';
    public $description= 'Initialize graphdb lucene full-text search for reconciliation';

    public function handle(GraphDbDriver $graphDb)
    {
        $this->info('Initialize graphdb lucene ...');

        $indexName=config('ldog.reconciliation.index');

        $graphDb->connect('open');
        $graphDb->rawUpdate("
            PREFIX luc: <http://www.ontotext.com/owlim/lucene#>
            
            INSERT DATA {
              luc:index luc:setParam 'uris' .
              luc:include luc:setParam 'literals' .
              luc:moleculeSize luc:setParam '2' .
              luc:includePredicates luc:setParam 'http://www.w3.org/2000/01/rdf-schema#label' .
              luc:$indexName luc:createIndex 'true' . 
            }            
        ");

        $this->info('Initialization of graphdb lucene is done successfully!!');
    }
}