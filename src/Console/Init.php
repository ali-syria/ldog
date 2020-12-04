<?php


namespace AliSyria\LDOG\Console;


use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\OrganizationManager\Employee;
use Illuminate\Console\Command;

class Init extends Command
{

    protected $signature= 'ldog:init';
    protected $description= 'Initialize The Platform';

    public function handle()
    {
        $this->info('Initializing The Platform ...');
        OntologyManager::importLdogOntology();
        $this->info('Ldog ontology has been imported successfully!');
        OntologyManager::importConversionOntology();
        $this->info('Conversion ontology has been imported successfully!');
        $this->call(InitGraphDbLuceneReconciliator::class);

        $cabinet=Cabinet::create(null,config('ldog.cabinet.name'),config('ldog.cabinet.description'),
            config('ldog.cabinet.logoUrl'));
        $loginAccount=User::create(config('ldog.cabinet.admin_username'),config('ldog.cabinet.admin_password'));
        $employee=Employee::create($cabinet,$loginAccount,'0',config('ldog.cabinet.admin_name'),
            config('ldog.cabinet.admin_description'));
        $this->info('Cabinet has been created Successfully!');

        $this->info('Initialization Done Successfully!!');
    }
}