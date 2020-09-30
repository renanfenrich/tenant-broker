<?php

namespace RenanFenrich\TenantBroker;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\RouteCollection;
use RenanFenrich\TenantBroker\TenantBroker;
use Illuminate\Routing\UrlGenerator as CoreUrlGenerator;

class UrlGenerator extends CoreUrlGenerator
{
    /**
     * @var TenantBroker
     */
    protected $tenant;

    /**
     * Create a new URL Generator instance.
     *
     * @param  \Illuminate\Routing\RouteCollection $routes
     * @param  \Illuminate\Http\Request $request
     * @param TenantBroker $tenant
     */
    public function __construct(RouteCollection $routes, Request $request, TenantBroker $tenant, $assetRoot = null)
    {
        parent::__construct($routes, $request, $assetRoot = null);

        $this->tenant = $tenant;
    }

    /**
     * Get the URL to a named route.
     *
     * @param  string  $name
     * @param  mixed   $parameters
     * @param  bool  $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        if (!is_null($route = $this->routes->getByName($name))) {
            $actions = $route->getAction();

            if (
                isset($actions['domain'])
                && $actions['domain'] == $this->tenant->getFullDomain()
            ) {
                $parameters = $this->mergeSubDomainParameters($route, $parameters);
            }

            return $this->toRoute($route, $parameters, $absolute);
        }

        throw new InvalidArgumentException("Route [{$name}] not defined.");
    }

    /**
     * Merge user parameters with subdomain parameter
     *
     * @param array|string $parameters
     * @return array array of parameters
     */
    protected function mergeSubDomainParameters($route, $parameters = [])
    {
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        if ($param = $this->request->route()->parameter(config('tenant.param'))) {
            return array_replace($parameters, [config('tenant.param') => $param]);
        }

        if ($domain = $this->extractDomainFromUrl()) {
            $parameters = array_replace($parameters, [config('tenant.param') => $domain]);
        }

        return $parameters;
    }

    /**
     * Extract the domain from url
     *
     * @return string subdomain parameter value
     */
    private function extractDomainFromUrl()
    {
        if ($this->request->getHost() !== $this->tenant->getDomain()) {
            return str_ireplace(".{$this->tenant->getDomain()}", '', $this->request->getHost());
        }

        return false;
    }
}
