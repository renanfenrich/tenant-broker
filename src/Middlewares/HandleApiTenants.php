<?php

namespace RenanFenrich\TenantBroker\Middlewares;

use Closure;
use RenanFenrich\TenantBroker\TenantBroker;
use Illuminate\Contracts\Routing\UrlGenerator;

class HandleApiTenants
{
    /**
     * @var TenantBroker
     */
    protected $tenant;

    /**
     * @var UrlGenerator
     */
    protected $url;

    public function __construct(TenantBroker $tenant, UrlGenerator $url)
    {
        $this->tenant = $tenant;
        $this->url = $url;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->tenant->validadeToken();

        if ($host = $this->tenant->getCurrentTenant('api')) {
            if ($this->tenant->reconnectDatabaseUsing($host)) {
                return $next($request);
            }
        }

        return $this->tenant->abort();
    }
}
