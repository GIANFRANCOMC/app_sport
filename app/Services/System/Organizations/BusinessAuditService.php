<?php

declare(strict_types=1);

namespace App\Services\System\Organizations;

use App\Models\System\Organizations\BusinessAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

final class BusinessAuditService {

    private const HIDDEN_FIELDS = [
        "password",
        "remember_token",
        "secret",
        "secret_hash",
        "access_token",
        "api_token",
        "fingerprint_template"
    ];

    public static function record(
        int $companyId,
        string $module,
        string $action,
        string $summary,
        ?Model $record = null,
        array $before = [],
        array $after = [],
        array $context = [],
        ?int $branchId = null,
        ?int $userId = null
    ): BusinessAuditLog {

        $request = app()->bound("request") ? request() : null;

        return BusinessAuditLog::create([
            "company_id" => $companyId,
            "branch_id" => $branchId,
            "user_id" => $userId ?? auth()->id(),
            "module" => $module,
            "action" => $action,
            "auditable_type" => $record ? $record::class : null,
            "auditable_id" => $record?->getKey(),
            "summary" => $summary,
            "before_data" => self::sanitize($before),
            "after_data" => self::sanitize($after),
            "context" => self::sanitize($context),
            "ip_address" => $request?->ip(),
            "user_agent" => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
            "occurred_at" => now()
        ]);

    }

    public static function recordModelChange(Model $model, string $action): ?BusinessAuditLog {

        $companyId = (int) ($model->getAttribute("company_id") ?? auth()->user()?->company_id ?? 0);
        if($companyId <= 0) {
            return null;
        }

        $before = $action === "created" ? [] : $model->getOriginal();
        $after = $action === "deleted" ? [] : $model->getAttributes();

        return self::record(
            $companyId,
            $model->getTable(),
            $action,
            sprintf("%s #%s: %s", $model->getTable(), (string) $model->getKey(), $action),
            $model,
            $before,
            $after,
            [],
            $model->getAttribute("branch_id") ? (int) $model->getAttribute("branch_id") : null
        );

    }

    private static function sanitize(array $data): array {

        foreach(self::HIDDEN_FIELDS as $field) {
            Arr::forget($data, $field);
        }

        return $data;

    }

}
