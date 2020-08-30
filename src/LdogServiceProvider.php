<?php


namespace AliSyria\LDOG;


use Illuminate\Support\ServiceProvider;

class LdogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php','ldog');
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