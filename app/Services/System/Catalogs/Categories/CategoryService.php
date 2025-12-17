<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Categories;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};

use App\Models\System\Catalogs\Category;

/**
 * Service class for managing Category operations
 * Handles business logic for creating and updating categories
 */
class CategoryService {

    /**
     * Translation namespace for category module
     */
    private const TRANSLATION_NAMESPACE = "System.Catalogs.category";

    /**
     * Allowed fields for category creation and update
     */
    private const ALLOWED_FIELDS = [
        "internal_code",
        "name",
        "description",
        "status"
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
     * Prepare category data for creation
     *
     * @param array $data Input data
     * @param int $companyId Company ID
     * @param int $userId User ID
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

            $categoryData[$field] = $data[$field] ?? null;

        }

        return $categoryData;

    }

    /**
     * Prepare category data for update (only changed fields)
     *
     * @param Category $category Category instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareCategoryDataForUpdate(Category $category, array $data): array {

        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field]) && $data[$field] !== $category->$field) {

                $updateData[$field] = $data[$field];

            }

        }

        return $updateData;

    }

    /**
     * Create a new category
     *
     * @param array $data Category data from request
     * @param int|null $userId User ID creating the category
     * @return Category|null Created category instance or null on failure
     * @throws \Exception
     */
    public static function create(array $data, ?int $userId = null): ?Category {

        $category = null;

        DB::transaction(function() use($data, $userId, &$category) {

            $userAuth  = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {

                throw new Exception(self::trans("company_id_required"));

            }

            $userId = $userId ?? $userAuth->id;

            // Check if internal code exists
            $categoryExists = Category::where("company_id", $companyId)
                                      ->where("internal_code", $data["internal_code"])
                                      ->exists();

            if($categoryExists) {

                throw new Exception(self::trans("internal_code_exists"));

            }

            // Prepare category data with only allowed fields
            $categoryData = self::prepareCategoryDataForCreate($data, $companyId, $userId);

            // Create the category
            $category = Category::create($categoryData);

        });

        return $category;

    }

    /**
     * Update an existing category
     *
     * @param Category $category Category instance to update
     * @param array $data Updated category data
     * @param int|null $userId User ID updating the category
     * @return Category Updated category instance
     * @throws \Exception
     */
    public static function update(Category $category, array $data, ?int $userId = null): Category {

        DB::transaction(function() use($category, $data, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id;

            // Check if internal code exists (excluding current category)
            if(isset($data["internal_code"])) {

                $categoryExists = Category::where("company_id", $category->company_id)
                                          ->where("internal_code", $data["internal_code"])
                                          ->where("id", "!=", $category->id)
                                          ->exists();

                if($categoryExists) {

                    throw new Exception(self::trans("internal_code_exists"));

                }

            }

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
     * Find category by ID and company ID
     *
     * @param int $id Category ID
     * @param int $companyId Company ID
     * @return Category|null
     */
    public static function findByIdAndCompany(int $id, int $companyId): ?Category {

        return Category::where("id", $id)
                       ->where("company_id", $companyId)
                       ->first();

    }

    /**
     * Get paginated list of categories
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15) {

        $query = Category::where("company_id", $companyId);

        // Apply search filters
        self::applySearchFilters($query, $filters);

        // Apply ordering
        $query->orderBy("name", "ASC");

        return $query->paginate($perPage);

    }

    /**
     * Apply search filters to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    private static function applySearchFilters($query, array $filters): void {

        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        if(!Utilities::isDefined($filterBy) || !Utilities::isDefined($word)) {

            return;

        }

        $searchTerm = Utilities::getWordSearch($word);
        $searchableFields = ["internal_code", "name", "description"];

        if($filterBy === "all") {

            // Search across all searchable fields
            $query->where(function($q) use($searchTerm, $searchableFields) {

                foreach($searchableFields as $field) {

                    $q->orWhere($field, "like", $searchTerm);

                }

            });

        }elseif(in_array($filterBy, $searchableFields, true)) {

            // Search in specific field
            $query->where($filterBy, "like", $searchTerm);

        }

    }

    /**
     * Check if internal code exists
     *
     * @param string $internalCode Internal code
     * @param int $companyId Company ID
     * @param int|null $excludeId Category ID to exclude
     * @return bool
     */
    public static function internalCodeExists(string $internalCode, int $companyId, ?int $excludeId = null): bool {

        $query = Category::where("company_id", $companyId)
                        ->where("internal_code", $internalCode);

        if($excludeId !== null) {

            $query->where("id", "!=", $excludeId);

        }

        return $query->exists();

    }

}

