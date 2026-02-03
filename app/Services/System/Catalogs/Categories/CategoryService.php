<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Categories;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

use App\Models\System\Catalogs\{Category};

/**
 * Service class for managing module operations
 * Handles business logic for creating and updating records
 */
class CategoryService {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Catalogs.category";

    /**
     * Allowed fields for record creation and update
     */
    private const ALLOWED_FIELDS = [
        "internal_code",
        "name",
        "description",
        "status"
    ];

    /**
     * Searchable fields for filtering
     */
    private const SEARCHABLE_FIELDS = [
        "internal_code",
        "name",
        "description"
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
    private static function prepareCategoryDataForCreate(array $data, int $companyId, int $userId): array {

        $categoryData = [
            "company_id" => $companyId,
            "status"     => $data["status"] ?? "active",
            "created_at" => now(),
            "created_by" => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field])) {

                $categoryData[$field] = $data[$field];

            }

        }

        return $categoryData;

    }

    /**
     * Prepare data for update (only changed fields)
     *
     * @param Category $category Record instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareCategoryDataForUpdate(Category $category, array $data): array {

        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field])) {

                if($data[$field] !== $category->$field) {

                    $updateData[$field] = $data[$field];

                }

            }

        }

        return $updateData;

    }

    /**
     * Create a new record
     *
     * @param array $data Input data
     * @param int|null $userId User creating the record
     * @return Category|null Created record instance or null on failure
     * @throws Exception
     */
    public static function create(array $data, ?int $userId = null): ?Category {

        $category = null;

        DB::transaction(function() use($data, $userId, &$category) {

            $userAuth  = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {

                throw new Exception(self::trans("company_id_required"));

            }

            $userId = $userId ?? $userAuth->id ?? null;

            // Prepare data with only allowed fields
            $categoryData = self::prepareCategoryDataForCreate($data, $companyId, $userId);

            // Create the record
            $category = Category::create($categoryData);

        });

        return $category;

    }

    /**
     * Update an existing record
     *
     * @param Category $category Record instance to update
     * @param array $data Input data
     * @param int|null $userId User updating the record
     * @return Category Updated record instance
     */
    public static function update(Category $category, array $data, ?int $userId = null): Category {

        DB::transaction(function() use($category, $data, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id ?? null;

            // Prepare update data with only changed fields
            $updateData = self::prepareCategoryDataForUpdate($category, $data);

            // Only update if there are changes
            if(!empty($updateData)) {

                $updateData["updated_at"] = now();
                $updateData["updated_by"] = $userId;
                $category->update($updateData);

            }

        });

        return $category->fresh();

    }

    /**
     * Find record by ID and company ID
     *
     * @param int $id Record
     * @param int $companyId Company
     * @param array|null $statuses Filter by statuses (e.g. ["active"], ["active", "inactive"])
     * @param array $relations Relations to eager load
     * @return Category|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, ?array $statuses = ["active"], array $relations = []): ?Category {

        $query = Category::where("id", $id)
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

        $query = Category::where("company_id", $companyId);

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

