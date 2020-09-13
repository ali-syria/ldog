<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\GraphStore\GraphDbDriver;
use AliSyria\LDOG\Tests\TestCase;

class GraphDbDriverTest extends TestCase
{
    private GraphDbDriver $graphDB;

    public function setUp(): void
    {
        parent::setUp();
        $this->graphDB=app(GraphDbDriver::class)->connect('open');
        $this->graphDB->clearAll();
    }

    public function testLoadIRIintoNamedGraph()
    {
        $this->graphDB
            ->loadIRIintoNamedGraph('http://dbpedia.org/data/Damascus.ttl','http://example/books');
    }
    public function testClearNamedGraph()
    {
        $this->graphDB->clearNamedGraph('http://example/books');
    }
}