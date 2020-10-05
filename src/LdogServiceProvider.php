<?php


namespace AliSyria\LDOG;


use AliSyria\LDOG\Authentication\GraphUserProvider;
use AliSyria\LDOG\Console\Init;
use AliSyria\LDOG\Console\InitGraphDbLuceneReconciliator;
use AliSyria\LDOG\Console\RefreshGraphDbLuceneIndex;
use AliSyria\LDOG\GraphStore\ConnectionFactory;
use AliSyria\LDOG\GraphStore\GraphDbDriver;
use AliSyria\LDOG\GraphStore\GraphStoreManager;
use AliSyria\LDOG\UriBuilder\Factory;
use AliSyria\LDOG\UriDereferencer\Dereferencer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LdogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php','ldog');

        $this->app->singleton('ldog.uri',function($app){
            return $app->make(Factory::class);
        });
        $this->app->singleton('graph_db',function($app){
            return $app->make(GraphDbDriver::class);
        });
        $this->app->singleton('ldog.gs.open',function($app){
            return ConnectionFactory::make('open');
        });
        $this->app->singleton('ldog.gs.secure',function($app){
            return ConnectionFactory::make('secure');
        });
        $this->app->singleton('ldog.gs.manager',function($app){
            return $app->make(GraphStoreManager::class);
        });
    }

    public function boot()
    {
        Request::macro('wantsRDF',function(){
            $acceptable = $this->getAcceptableContentTypes();

            return isset($acceptable[0]) && Str::contains($acceptable[0],
                    Dereferencer::getRDFmimeTypes());
        });
        $this->commands([
            Init::class,
            InitGraphDbLuceneReconciliator::class,
            RefreshGraphDbLuceneIndex::class,
        ]);

        if(!$this->app->runningInConsole())
        {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('ldog.php')
        ],'config');

        $this->loadRoutesFrom(__DIR__.'../../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views','ldog');

        $this->publishes([
            __DIR__.'/../resources/views'=> resource_path('views/vendor/ldog')
        ],'views');

        Auth::provider('ldog',function($app, array $config){
            return new GraphUserProvider();
        });
    }
}