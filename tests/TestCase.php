<?php


namespace AliSyria\LDOG\Tests;


use AliSyria\LDOG\LdogServiceProvider;

class TestCase extends Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

    }

    protected function getPackageProviders($app)
    {
        return [
            LdogServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}