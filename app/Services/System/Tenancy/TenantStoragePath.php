<?php

declare(strict_types=1);

namespace App\Services\System\Tenancy;

use RuntimeException;

final class TenantStoragePath {
    public static function for(string $relativePath): string {

        $tenant = app(TenantContext::class)->get();
        if(!$tenant) {

            throw new RuntimeException("No existe un contexto tenant activo para almacenar el archivo.");

        }

        $cleanPath = trim(str_replace("..", "", str_replace("\\", "/", $relativePath)), "/");

        return "tenants/{$tenant->slug}/{$cleanPath}";

    }
}
