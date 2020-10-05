<?php


namespace AliSyria\LDOG\Console;


use Illuminate\Console\Command;

class Init extends Command
{

    protected $signature= 'ldog:init';
    protected $description= 'Initialize The Platform';

    public function handle()
    {
        $this->info('Initializing The Platform ...');

        $this->info('Initialization Done Successfully!!');
    }
}