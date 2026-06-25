<?php

declare(strict_types=1);

namespace App\Services\System\Tenancy;

use App\Models\System\Tenancy\TenantDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

final class TenantConnectionManager {

    public function connect(TenantDatabase $tenant): void {

        $connectionName = config('tenancy.tenant_connection', 'tenant');
        $base = config("database.connections.{$connectionName}", config('database.connections.mysql'));

        $base['driver'] = $tenant->db_driver ?: ($base['driver'] ?? 'mysql');
        $base['host'] = $tenant->db_host ?: ($base['host'] ?? env('DB_HOST', '127.0.0.1'));
        $base['port'] = $tenant->db_port ?: ($base['port'] ?? env('DB_PORT', '3306'));
        $base['database'] = $tenant->database_name;
        $base['username'] = $tenant->db_username ?: ($base['username'] ?? env('DB_USERNAME'));
        $base['password'] = $tenant->db_password
            ? Crypt::decryptString($tenant->db_password)
            : ($base['password'] ?? env('DB_PASSWORD'));

        Config::set("database.connections.{$connectionName}", $base);
        Config::set('database.default', $connectionName);

        DB::purge($connectionName);
        DB::setDefaultConnection($connectionName);
        DB::reconnect($connectionName);

    }

    public function disconnect(): void {

        $connectionName = config('tenancy.tenant_connection', 'tenant');
        DB::purge($connectionName);
        Config::set('database.default', env('DB_CONNECTION', 'mysql'));
        DB::setDefaultConnection(config('database.default'));

    }

}
