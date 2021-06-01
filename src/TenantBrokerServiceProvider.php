<?php

namespace RenanFenrich\TenantBroker;

use Illuminate\Routing\Router;
use Illuminate\Routing\RoutingServiceProvider;
use RenanFenrich\TenantBroker\Middlewares\TenantDatabaseConnector;
use RenanFenrich\TenantBroker\TenantBroker;
use RenanFenrich\TenantBroker\UrlGenerator;

class TenantBrokerServiceProvider extends RoutingServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tenant.php', 'tenant');

        // Register the service the package provides.
        $this->app->singleton('tenantbroker', function ($app) {
            return new TenantBroker(
                $app['config'],
                $app['db'],
                $app->rebinding('request', $this->requestRebinder())
            );
        });

        $this->app->tag(['tenantbroker'], 'tenant');

        $this->registerMiddleware();

        $this->registerUrlGenerator();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['tenantbroker'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/tenant.php' => config_path('tenant.php'),
        ], 'tenant.config');
    }

    /**
     * Override the UrlGenerator
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);

            return new UrlGenerator(
                $routes,
                $app->rebinding(
                    'request',
                    $this->requestRebinder()
                ),
                $this->app->make('tenantbroker'),
                $app['config']['app.asset_url']
            );
        });
    }

    protected function registerMiddleware()
    {
        $middlewareGroups = $this->app['config']->get('tenant.middleware_groups');

        foreach ($middlewareGroups as $group) {
            $this->app['router']
                ->prependMiddlewareToGroup(
                    $group,
                    TenantDatabaseConnector::class
                );
        }
    }
}
