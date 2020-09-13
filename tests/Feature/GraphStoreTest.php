<?php


namespace AliSyria\LDOG\Tests\Feature;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Tests\TestCase;

class GraphStoreTest extends TestCase
{
    public function testOpenConnectionIsDefault()
    {
        $openRepo='open_rep';

        config([
            'ldog.graph_stores.open.repository'=> $openRepo
        ]);

        $this->assertEquals($openRepo,GS::getConnection()->getRepository());
    }
}