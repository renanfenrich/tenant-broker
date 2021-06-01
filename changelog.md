# Changelog

All notable changes to `TenantBroker` will be documented in this file.

## Version 1.0

### Added
- Everything

## Version 1.2.0

### Refactor
- COMPLETE refactoring of the provider class and dependencies
- Configuration file: local now gets it's value from env('BROKER_LOCAL')
- Configuration file: header_host was renamed to header_alias
- Configuration file: domain was removed, retrieved from resquest host

### Feature
- Middlewares was merged into a single class and can be auto registered from config

### Fix
- Tenant connection now is merged in the default connection from driver
