<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\OuterLinkage\SilkOutLinker;
use AliSyria\LDOG\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class SilkOutLinkerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();

        OntologyManager::importLdogOntology();
    }

    public function testPerformLinkage()
    {
        $silkLslPath='spec-LSL.xml';
        $diskName=config('ldog.storage.disk');
        $disk=Storage::disk($diskName);
        $disk->put('spec-LSL.xml',file_get_contents(__DIR__.'/../Datasets/Silk/'.$silkLslPath));

        $this->assertTrue((new SilkOutLinker($diskName,$silkLslPath))->performLinkage());
    }
}