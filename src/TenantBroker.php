<?php

namespace RenanFenrich\TenantBroker;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\DatabaseManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Auth\AuthenticationException;

class TenantBroker
{
    /**
     * The request instance.
     */
    public $request;

    /**
     * The tenant.
     */
    public $alias;
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The config repository instance.
     *
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $database;

    /**
     * Tenant host from request.
     *
     * @var string
     */
    protected $domain;

    public function __construct(
        Config $config,
        DatabaseManager $database,
        Request $request
    ) {
        $this->config = $config;
        $this->database = $database;
        $this->request = $request;
    }

    /**
     * Reconnect database using tenant.
     *
     * @param string $domain
     */
    public function reconnectDatabaseUsing($domain)
    {
        if ($tenant = $this->getTenant($domain)) {
            $config = $this->mergeDatabaseConfig($tenant['database']);

            $this->config->set('database.connections.tenant', $config);

            $this->database->setDefaultConnection('tenant');

            return $this->database->reconnect();
        }

        return false;
    }

    /**
     * Return default not found page with custom message.
     *
     * @return \Illuminate\Http\Response
     */
    public function abort()
    {
        return new Response([
            'message' => $this->config->get('tenant.not_found'),
        ], 404, ['content-type' => 'application/json']);
    }

    /**
     * Return tenant configuration from broker database.
     *
     * @param string $domain
     */
    public function getTenant($domain)
    {
        if ($this->config->get('tenant.local')) {
            return $this->getTenantFromFile($domain);
        }

        return $this->getTenantFromDatabase($domain);
    }

    /**
     * Get application root domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->config->get('tenant.domain');
    }

    /**
     * Generate route domain wildcard.
     *
     * @return string
     */
    public function getFullDomain()
    {
        $param = $this->config->get('tenant.param');
        $domain = $this->config->get('tenant.domain');

        return sprintf('{%s}.%s', $param, $domain);
    }

    /**
     * Check request for tenant host.
     *
     * @param [type] $guard
     */
    public function getCurrentTenant($guard)
    {
        if ('api' == $guard) {
            return $this->request->header(
                $this->config->get('tenant.header_host'),
                null
            );
        }

        return $this->request->getHost();
    }

    /**
     * Check for valid tenant broker token.
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function validadeToken()
    {
        try {
            $token = $this->getTokenFromRequest();

            if ($token !== $this->config->get('tenant.token')) {
                throw new AuthenticationException('Invalid Tenant API token');
            }
        } catch (AuthenticationException $e) {
            throw $e;
        }
    }

    /**
     * Get tenant configuration from broker database.
     *
     * @param string $domain
     *
     * @return null|array
     */
    protected function getTenantFromDatabase($domain)
    {
        $this->configureBrokerDatabase();

        $tenant = DB::table('tenants')->where('tenants.domain', $domain)->first();

        if ($tenant) {
            $database = DB::table('databases')
                ->select('host', 'username', 'password', 'database')
                ->where('tenant_id', $tenant->id)->first();

            if ($database) {
                $config = (array) $tenant + ['database' => (array) $database];

                $this->config->set('tenant.active', $config);

                return $config;
            }
        }
    }

    /**
     * Get tenant configuration from local config folder.
     *
     * @param string $domain
     *
     * @return null|array
     */
    protected function getTenantFromFile($domain)
    {
        if ($config = $this->config->get("tenants.{$domain}")) {
            return $config;
        }
    }

    /**
     * Get api token from request.
     *
     * @param Request $request
     */
    protected function getTokenFromRequest()
    {
        return $this->request->header(
            $this->config->get('tenant.header_token'),
            null
        );
    }

    /**
     * Merge current configuration with default connection.
     *
     * @param array $config
     *
     * @return array
     */
    protected function mergeDatabaseConfig($config = [])
    {
        $default = $this->database->connection()->getConfig();

        return array_merge($default, $config);
    }

    /**
     * Configure database to connect with broker.
     */
    private function configureBrokerDatabase()
    {
        $connection = $this->config->get('tenant.connection');
        $database = $this->config->get('tenant.database');

        $config = $this->mergeDatabaseConfig($database);

        $this->config->set("database.connections.{$connection}", $config);

        $this->database->setDefaultConnection($connection);
        $this->database->reconnect();
    }
}
