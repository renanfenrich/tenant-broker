# Tenant Broker Connector

## Installation


``` bash
$ composer require renanfenrich/tenant-broker
```
## Configuration

Publish configuration file

``` bash
$ php artisan vendor:publish --tag tenant.config

```

Configuration files for local development should be created at "config/tenants/".

Example:

``` php
<?php

return [
    'alias' => 'nasa',
    'connection' => 'mysql',
    'domain' => 'nasa.gov.us',
    'database' => [
        'host' => 'mysql.nasa.gov.us',
        'database' => 'jpl',
        'username' => 'root',
        'password' => 'voyager1',
    ]
];


```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email renan.fenrich@gmail.com instead of using the issue tracker.

## License

MIT. Please see the [license file](license.md) for more information.