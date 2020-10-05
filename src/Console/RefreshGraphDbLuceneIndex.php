<?php


namespace AliSyria\LDOG\Console;


use AliSyria\LDOG\GraphStore\GraphDbDriver;
use Illuminate\Console\Command;

class RefreshGraphDbLuceneIndex extends Command
{
    public $signature= 'ldog:refresh-graphdb-lucene';

    public $description= 'Causes all resources not currently indexed by to get indexed';

    public function handle(GraphDbDriver $graphDb)
    {
        $this->info('Refreshing graphdb lucene index ...');

        $indexName=config('ldog.reconciliation.index');

        $graphDb->connect('open');
        $graphDb->rawUpdate("
            PREFIX luc: <http://www.ontotext.com/owlim/lucene#>
            
            INSERT DATA {
              luc:$indexName luc:updateIndex _:b1 . 
            }            
        ");

        $this->info('Refreshing graphdb lucene index completed successfully!!');
    }
}