<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Users;

use App\Services\System\Base\{BaseConfigService, CompanyReferenceDataService};
use stdClass;

final class UserAttendanceConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "user_attendance";

    }

    protected static function usesUserScopedCache(): bool {

        return true;

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        $references = CompanyReferenceDataService::for($companyId);

        return self::data([
            "branches" => $references->activeBranches(),
            "users" => $references->users(),
            "sourceTypes" => collect(UserAttendanceService::sourceTypes())->map(fn($source) => [
                "code" => $source,
                "label" => match($source) {
                    "manual_form" => "Manual",
                    "qr_camera" => "Cámara QR",
                    "qr_scanner" => "Lector QR",
                    "biometric" => "Biométrico",
                    default => "Sistema"
                }
            ])->values(),
            "statuses" => [
                ["code" => "active", "label" => "En curso"],
                ["code" => "finalized", "label" => "Finalizada"],
                ["code" => "canceled", "label" => "Cancelada"]
            ]
        ]);

    }

}
