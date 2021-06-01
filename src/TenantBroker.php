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
     * The config repository instance.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * The database manager instance.
     *
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $database;

    /**
     * Request host sub and domain parts
     *
     * @var array
     */
    public $host;

    /**
     * The HTTP request instance.
     */
    public $request;

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
     * @param string $alias
     */
    public function reconnectDatabaseUsing($alias)
    {
        if ($tenant = $this->getTenantConfig($alias)) {
            $config = $this->mergeDatabaseConfig($tenant['database']);
            $connection = $tenant['connection'];

            $this->config->set("database.connections.$connection", $config);

            $this->database->setDefaultConnection($connection);

            return $this->database->reconnect($connection);
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
     * @param string $alias
     */
    public function getTenantConfig($alias)
    {
        if ($this->config->get('tenant.local')) {
            return $this->getConfigFromFile($alias);
        }

        return $this->getConfigFromDatabase($alias);
    }

    /**
     * Get application host.
     *
     * @return string
     */
    private function setHost()
    {
        $host = $this->request->getHost();

        $parts = explode('.', $host, 2);

        $this->host = array_combine(['alias', 'domain'], $parts);
    }

    /**
     * Get tenant alias from host
     *
     * @return void
     */
    public function getAlias()
    {
        return $this->host['alias'];
    }

    /**
     * Get domain from host
     *
     * @return void
     */
    public function getDomain()
    {
        return $this->host['domain'];
    }

    /**
     * Generate route host wildcard.
     *
     * @return string
     */
    public function getDomainRoute()
    {
        $param = $this->config->get('tenant.param');
        $host = $this->getDomain();

        return sprintf('{%s}.%s', $param, $host);
    }

    /**
     * Check request for tenant host.
     *
     * @return string
     */
    public function getCurrentTenant()
    {
        $this->setHost();

        if ($this->request->expectsJson()) {
            return $this->getTenantFromHeader();
        }

        return $this->getTenantFromRequest();
    }

    /**
     * Return tenant name from header
     *
     * @return void
     */
    private function getTenantFromHeader()
    {
        return $this->request->header(
            $this->config->get('tenant.header_alias'),
            null
        );
    }

    /**
     * Return tenant name from request host
     *
     * @return void
     */
    private function getTenantFromRequest()
    {
        return $this->host['alias'];
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
     * @param string $alias
     *
     * @return null|array
     */
    protected function getConfigFromDatabase($alias)
    {
        $this->configureBrokerDatabase();

        $tenant = DB::table('tenants')->where('tenants.alias', $alias)->first();

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
     * @param string $alias
     *
     * @return null|array
     */
    protected function getConfigFromFile($alias)
    {
        if ($config = $this->config->get("tenants.{$alias}")) {
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
