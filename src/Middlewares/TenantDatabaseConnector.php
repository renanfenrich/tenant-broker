<?php

namespace RenanFenrich\TenantBroker\Middlewares;

use Closure;
use RenanFenrich\TenantBroker\TenantBroker;

class TenantDatabaseConnector
{
    /**
     * @var TenantBroker
     */
    protected $tenant;

    public function __construct(TenantBroker $tenant)
    {
        $this->tenant = $tenant;
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

        if ($host = $this->tenant->getCurrentTenant()) {
            if ($this->tenant->reconnectDatabaseUsing($host)) {
                return $next($request);
            }
        }

        return $this->tenant->abort();
    }
}
