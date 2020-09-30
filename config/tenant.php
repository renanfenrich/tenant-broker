<?php

return [
    // Use local tenant configuration files
    'local' => true,

    // Not found return message
    'not_found' => 'Tenant not found',

    // Header names passed by broker applications
    'header_host' => 'x-tenant-host',
    'header_token' => 'x-tenant-token',

    'token' => env('BROKER_API_TOKEN', null),

    // Name for auto bind tenant route parameter
    'param' => 'tenant',
    'domain' => env('BROKER_DOMAIN', 'localhost'),

    // Broker connection driver
    'connection' => 'mysql',

    // Broker database configurations
    'database' => [
        'host' => env('BROKER_DB_HOST', 'localhost'),
        'username' => env('BROKER_DB_USERNAME', 'root'),
        'password' => env('BROKER_DB_PASSWORD', ''),
        'database' => env('BROKER_DB_DATABASE', ''),
    ]
];
