<?php

declare(strict_types=1);

namespace App\Services\System\Tenancy;

use App\Models\System\Tenancy\{TenantDatabase};
use Illuminate\Support\Facades\{Artisan};
use RuntimeException;

final class PlatformTenantProvisioner {
    public function __construct(private readonly TenantConnectionManager $connections) {
    }

    public function create(array $data): TenantDatabase {

        try {

            $exitCode = Artisan::call("tenant:create", [
                "slug" => strtolower($data["slug"]),
                "--commercial-name" => $data["commercial_name"],
                "--legal-name" => $data["legal_name"],
                "--document-number" => $data["document_number"],
                "--admin-name" => $data["admin_name"],
                "--admin-email" => $data["admin_email"],
                "--admin-password" => $data["admin_password"],
                "--skip-cache-clear" => true,
            ]);

        } finally {

            $this->connections->disconnect();

        }

        if($exitCode !== 0) {

            throw new RuntimeException(trim(Artisan::output()) ?: "No se pudo crear el tenant.");

        }

        return TenantDatabase::query()
            ->with("domains")
            ->where("slug", strtolower($data["slug"]))
            ->firstOrFail();

    }
}
