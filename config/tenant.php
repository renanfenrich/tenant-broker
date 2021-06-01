<?php

return [
    // Use local tenant configuration files
    'local' => env('BROKER_LOCAL', true),

    // Not found return message
    'not_found' => 'Tenant not found',

    // globally prepend midleware to the specified groups
    'middleware_groups' => [
        'api', 'web'
    ],

    // Headers passed by broker applications
    'header_alias' => 'x-tenant-alias',
    'header_token' => 'x-tenant-token',

    'token' => env('BROKER_API_TOKEN', null),

    // Name for auto bind tenant route parameter
    'param' => 'tenant',

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
