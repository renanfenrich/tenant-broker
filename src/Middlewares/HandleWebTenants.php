<?php

namespace RenanFenrich\TenantBroker\Middlewares;

use Closure;
use RenanFenrich\TenantBroker\TenantBroker;
use Illuminate\Contracts\Routing\UrlGenerator;

class HandleWebTenants
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
        if ($domain = $this->tenant->getCurrentTenant('web')) {
            if ($this->tenant->reconnectDatabaseUsing($domain)) {
                return $next($request);
            }
        }

        return $this->tenant->abort();
    }
}
