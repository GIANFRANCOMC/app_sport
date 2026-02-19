<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Users;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

use App\Models\System\Organizations\{User};

/**
 * Service class for managing module operations
 * Handles business logic for creating and updating records
 */
class UserService {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Organizations.user";

    /**
     * Allowed fields for record creation and update
     */
    private const ALLOWED_FIELDS = [
        "role_id",
        "identity_document_type_id",
        "document_number",
        "name",
        "email",
        "phone_number",
        "gender",
        "birthdate",
        "status"
    ];

    /**
     * Searchable fields for filtering
     */
    private const SEARCHABLE_FIELDS = [
        "document_number",
        "name",
        "email",
        "phone_number"
    ];

    /**
     * Get translation with fallback
     *
     * @param string $key Translation key
     * @param array $replace Replacements
     * @return string
     */
    private static function trans(string $key, array $replace = []): string {

        return TranslationHelper::getWithFallback(self::TRANSLATION_NAMESPACE, $key, $replace);

    }

    /**
     * Prepare data for creation
     *
     * @param array $data Input data
     * @param int $companyId Company
     * @param int $userId User
     * @return array
     */
    private static function prepareUserDataForCreate(array $data, int $companyId, int $userId): array {

        $userData = [
            "company_id" => $companyId,
            "gender"     => $data["gender"] ?? "other",
            "status"     => $data["status"] ?? "active",
            "created_at" => now(),
            "created_by" => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field])) {

                $userData[$field] = $field === "email" ? Str::lower($data[$field]) : $data[$field];

            }

        }

        // Handle password separately (it's hashed automatically by Laravel)
        if(isset($data["password"])) {

            $userData["password"] = $data["password"];

        }

        return $userData;

    }

    /**
     * Prepare data for update (only changed fields)
     *
     * @param User $user Record instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareUserDataForUpdate(User $user, array $data): array {

        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field])) {

                $value = $field === "email" ? Str::lower($data[$field]) : $data[$field];

                if($value !== $user->$field) {

                    $updateData[$field] = $value;

                }
            }

        }

        // Handle password separately (only if provided)
        if(isset($data["password"]) && !empty($data["password"])) {

            $updateData["password"] = $data["password"];

        }

        return $updateData;

    }

    /**
     * Create a new record
     *
     * @param array $data Input data
     * @param int|null $userId User creating the record
     * @return User|null Created record instance or null on failure
     * @throws Exception
     */
    public static function create(array $data, ?int $userId = null): ?User {

        $user = null;

        DB::transaction(function() use($data, $userId, &$user) {

            $userAuth  = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {

                throw new Exception(self::trans("company_id_required"));

            }

            $userId = $userId ?? $userAuth->id ?? null;

            // Prepare data with only allowed fields
            $userData = self::prepareUserDataForCreate($data, $companyId, $userId);

            // Create the record
            $user = User::create($userData);

        });

        return $user;

    }

    /**
     * Update an existing record
     *
     * @param User $user Record instance to update
     * @param array $data Input data
     * @param int|null $userId User updating the record
     * @return User Updated record instance
     */
    public static function update(User $user, array $data, ?int $userId = null): User {

        DB::transaction(function() use($user, $data, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id ?? null;

            // Prepare update data with only changed fields
            $updateData = self::prepareUserDataForUpdate($user, $data);

            // Only update if there are changes
            if(!empty($updateData)) {

                $updateData["updated_at"] = now();
                $updateData["updated_by"] = $userId;
                $user->update($updateData);

            }

        });

        return $user->fresh(["identityDocumentType", "role"]);

    }

    /**
     * Find record by ID and company ID
     *
     * @param int $id Record
     * @param int $companyId Company
     * @param array|null $statuses Filter by statuses (e.g. ["active"], ["active", "inactive"])
     * @param array $relations Relations to eager load
     * @return User|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, ?array $statuses = ["active"], array $relations = ["identityDocumentType", "role"]): ?User {

        $query = User::where("id", $id)
                     ->where("company_id", $companyId);

        if($statuses !== null && !empty($statuses)) {

            $query->whereIn("status", $statuses);

        }

        if($relations !== null && !empty($relations)) {

            $query->with($relations);

        }

        return $query->first();

    }

    /**
     * Get paginated list of records with filters
     *
     * @param int $companyId Company
     * @param array $filters Filter parameters (filter_by, word)
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator {

        $query = User::where("company_id", $companyId)
                     ->with(["identityDocumentType", "role"]);

        // Apply filters
        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        if(Utilities::isDefined($word) && Utilities::isDefined($filterBy)) {

            $searchTerm = Utilities::getWordSearch($word);

            if($filterBy === "all") {

                // Search across all searchable fields
                $query->where(function(Builder $q) use($searchTerm) {

                    $searchableFields = self::SEARCHABLE_FIELDS;
                    $firstField       = array_shift($searchableFields);

                    if($firstField) {

                        $q->where($firstField, "like", $searchTerm);

                    }

                    foreach($searchableFields as $field) {

                        $q->orWhere($field, "like", $searchTerm);

                    }

                });

            }elseif(in_array($filterBy, self::SEARCHABLE_FIELDS, true)) {

                // Search in specific field
                $query->where($filterBy, "like", $searchTerm);

            }

        }

        return $query->orderBy("name", "ASC")
                     ->paginate($perPage);

    }

}
