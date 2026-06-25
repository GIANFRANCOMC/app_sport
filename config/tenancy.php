<?php

declare(strict_types=1);

return [
    'central_domains' => array_filter(array_map(
        static fn(string $domain): string => strtolower(trim($domain)),
        explode(',', env('TENANCY_CENTRAL_DOMAINS', 'localhost,127.0.0.1,gympe.test'))
    )),

    'base_domain' => env('TENANCY_BASE_DOMAIN', 'gympe.test'),

    'tenant_connection' => env('TENANT_DB_CONNECTION', 'tenant'),

    'landlord_connection' => env('LANDLORD_DB_CONNECTION', 'landlord'),

    'database_prefix' => env('TENANT_DB_PREFIX', 'gympe_tenant_'),

    'session_cookie_prefix' => env('TENANT_SESSION_COOKIE_PREFIX', 'gympe_tenant'),
];
