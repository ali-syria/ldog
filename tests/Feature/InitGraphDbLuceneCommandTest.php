<?php


namespace AliSyria\LDOG\Tests\Feature;

use AliSyria\LDOG\GraphStore\GraphDbDriver;
use AliSyria\LDOG\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class InitGraphDbLuceneCommandTest extends TestCase
{
    private GraphDbDriver $graphDB;

    public function setUp(): void
    {
        parent::setUp();
        $this->graphDB=app(GraphDbDriver::class)->connect('open');
        $this->graphDB->clearAll();
    }

    public function testItCreatesLuceneIndexBasedOnConfigurations()
    {
        $indexName=config('ldog.reconciliation.index');
        $indexUri="http://www.ontotext.com/owlim/lucene#$indexName";

        $this->assertFalse($this->graphDB->isResourceExist($indexUri));

        Artisan::call('ldog:init-graphdb-lucene');
        sleep(10);
        $this->assertTrue($this->graphDB->isResourceExist($indexUri));
    }
}