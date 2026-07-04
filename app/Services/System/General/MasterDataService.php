<?php

declare(strict_types=1);

namespace App\Services\System\General;

use App\Models\System\General\{Currency, DocumentType, IdentityDocumentType};
use App\Services\System\Base\MasterReferenceDataService;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class MasterDataService {

    private const DEFINITIONS = [
        "identity-documents" => ["model" => IdentityDocumentType::class, "table" => "identity_document_types"],
        "document-types" => ["model" => DocumentType::class, "table" => "document_types"],
        "currencies" => ["model" => Currency::class, "table" => "currencies"]
    ];

    public static function list(int $companyId, string $resource) {

        $definition = self::definition($resource);

        return $definition["model"]::query()
            ->where("company_id", $companyId)
            ->orderBy($resource === "currencies" ? "code" : "name")
            ->get();

    }

    public static function save(
        int $companyId,
        int $userId,
        string $resource,
        array $data,
        ?int $id = null
    ): Model {

        $definition = self::definition($resource);

        return DB::transaction(function() use($companyId, $userId, $resource, $definition, $data, $id) {

            $record = $id
                ? $definition["model"]::query()->where("company_id", $companyId)->findOrFail($id)
                : new $definition["model"]();

            $duplicate = $definition["model"]::query()
                ->where("company_id", $companyId)
                ->where("code", $data["code"])
                ->when($id, fn($query) => $query->where("id", "!=", $id))
                ->exists();

            if($duplicate) {

                throw new DomainException("Ya existe un registro con ese código en la empresa.");

            }

            if(($data["status"] ?? $record->status) === "inactive" && $id) {

                self::assertCanDeactivate($companyId, $resource, $id);

            }

            $allowed = match($resource) {
                "identity-documents" => ["code", "name", "is_searchable", "min_length", "max_length", "status"],
                "currencies" => ["code", "sign", "singular_name", "plural_name", "status"],
                default => ["code", "name", "status"]
            };

            $record->fill(collect($data)->only($allowed)->all());
            $record->company_id = $companyId;
            $record->{$id ? "updated_by" : "created_by"} = $userId;
            $record->save();

            MasterReferenceDataService::clearCache($companyId);

            return $record->fresh();

        });

    }

    private static function assertCanDeactivate(int $companyId, string $resource, int $id): void {

        $references = match($resource) {
            "identity-documents" => [
                ["companies", "identity_document_type_id"],
                ["customers", "identity_document_type_id"],
                ["users", "identity_document_type_id"]
            ],
            "document-types" => [["series", "document_type_id"]],
            "currencies" => [
                ["companies", "currency_id"],
                ["items", "currency_id"],
                ["sales_header", "currency_id"],
                ["purchase_headers", "currency_id"]
            ],
            default => []
        };

        foreach($references as [$table, $column]) {

            if(DB::table($table)
                ->where($column, $id)
                ->where(function($query) use($companyId, $table) {

                    if($table !== "companies") {

                        $query->where("company_id", $companyId);

                    }else {

                        $query->where("id", $companyId);

                    }

                })
                ->exists()) {

                throw new DomainException("No se puede inactivar el registro porque está siendo utilizado.");

            }

        }

    }

    private static function definition(string $resource): array {

        return self::DEFINITIONS[$resource]
            ?? throw new DomainException("El maestro solicitado no está disponible.");

    }

}
