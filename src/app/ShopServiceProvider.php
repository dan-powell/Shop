<?php namespace DanPowell\Shop;

use DanPowell\Shop\Console\Commands\Seed;
use DanPowell\Shop\Console\Commands\AddUser;

class ShopServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {

        $this->app->register('DanPowell\Shop\Providers\ViewComposerServiceProvider');

        // Create new instances of each command when called
        $this->app->bindShared('command.shop.adduser', function ($app) {
            return new AddUser();
        });

        // Merge configs
        $this->mergeConfigFrom(
            __DIR__.'/../config/shop.php', 'shop'
        );

        // Include package routes
        if (!$this->app->routesAreCached()) {
            include __DIR__.'/Http/routes.php';
        }

        // Tell Laravel where to load the views from
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shop');

    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {

        // Setup some commands
        $this->commands('command.shop.adduser');

        // Publish Frontend Assets
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/shop'),
        ], 'public');

        // Publish Views
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/shop'),
        ], 'views');

        // Publish Config
        $this->publishes([
            __DIR__.'/../config/shop.php' => config_path('shop.php'),
        ], 'configs');

        // Publish Migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => $this->app->databasePath().'/migrations',
        ], 'migrations');

        // Publish Factories
        $this->publishes([
            __DIR__.'/../database/factories' => $this->app->databasePath().'/factories',
        ], 'factories');

        // Publish Seeds
        $this->publishes([
            __DIR__.'/../database/seeds' => $this->app->databasePath().'/seeds',
        ], 'seeds');


        // Publish Tests
        $this->publishes([
            __DIR__.'/../tests' => base_path('tests'),
        ], 'tests');

        // Publishes all stuff for dev
        $this->publishes([
            __DIR__.'/../database/seeds' => $this->app->databasePath().'/seeds',
            __DIR__.'/../database/migrations' => $this->app->databasePath().'/migrations',
            __DIR__.'/../database/factories' => $this->app->databasePath().'/factories',
            __DIR__.'/../tests' => base_path('tests'),
        ], 'dev');


    }

}