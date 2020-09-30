<?php

namespace RenanFenrich\TenantBroker\Facades;

use Illuminate\Support\Facades\Facade;

class TenantBroker extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'tenantbroker';
    }
}
