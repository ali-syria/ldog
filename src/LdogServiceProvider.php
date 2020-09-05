<?php


namespace AliSyria\LDOG;


use AliSyria\LDOG\UriBuilder\Factory;
use Illuminate\Support\ServiceProvider;

class LdogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php','ldog');

        $this->app->bind('ldog.uri',function($app){
            return $app->make(Factory::class);
        });
    }

    public function boot()
    {
        if($this->app->runningInConsole())
        {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('ldog.php')
            ],'config');
        }
    }
}