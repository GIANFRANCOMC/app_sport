<?php

declare(strict_types=1);

namespace App\Services\System\Customers;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

use App\Models\System\Customers\Customer;

/**
 * Service class for managing module operations
 * Handles business logic for creating and updating records
 */
class CustomerService {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Customers.customer";

    /**
     * Allowed fields for record creation and update
     */
    private const ALLOWED_FIELDS = [
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
    private static function prepareCustomerDataForCreate(array $data, int $companyId, int $userId): array {

        $customerData = [
            "company_id" => $companyId,
            "status"     => $data["status"] ?? "active",
            "created_at" => now(),
            "created_by" => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field])) {

                $customerData[$field] = $data[$field];

            }

        }

        return $customerData;

    }

    /**
     * Prepare data for update (only changed fields)
     *
     * @param Customer $customer Record instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareCustomerDataForUpdate(Customer $customer, array $data): array {

        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field]) && $data[$field] !== $customer->$field) {

                $updateData[$field] = $data[$field];

            }

        }

        return $updateData;

    }

    /**
     * Create a new record
     *
     * @param array $data Input data
     * @param int|null $userId User creating the record
     * @return Customer|null Created record instance or null on failure
     * @throws Exception
     */
    public static function create(array $data, ?int $userId = null): ?Customer {

        $customer = null;

        DB::transaction(function() use($data, $userId, &$customer) {

            $userAuth  = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {

                throw new Exception(self::trans("company_id_required"));

            }

            $userId = $userId ?? $userAuth->id ?? null;

            // Check if document number exists
            if(self::documentNumberExists($data["document_number"], $companyId)) {

                throw new Exception(self::trans("document_number_exists"));

            }

            // Prepare data with only allowed fields
            $customerData = self::prepareCustomerDataForCreate($data, $companyId, $userId);

            // Create the record
            $customer = Customer::create($customerData);

        });

        return $customer;

    }

    /**
     * Update an existing record
     *
     * @param Customer $customer Record instance to update
     * @param array $data Input data
     * @param int|null $userId User updating the record
     * @return Customer Updated record instance
     */
    public static function update(Customer $customer, array $data, ?int $userId = null): Customer {

        DB::transaction(function() use($customer, $data, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id ?? null;

            // Check if document number exists (excluding current customer)
            if(isset($data["document_number"])) {

                if(self::documentNumberExists($data["document_number"], $customer->company_id, $customer->id)) {

                    throw new Exception(self::trans("document_number_exists"));

                }

            }

            // Prepare update data with only changed fields
            $updateData = self::prepareCustomerDataForUpdate($customer, $data);

            // Only update if there are changes
            if(!empty($updateData)) {

                $updateData["updated_at"] = now();
                $updateData["updated_by"] = $userId;
                $customer->update($updateData);

            }

        });

        return $customer->fresh(["identityDocumentType"]);

    }

    /**
     * Find record by ID and company ID
     *
     * @param int $id Record
     * @param int $companyId Company
     * @param bool $activeOnly Only search active records
     * @param array $relations Relations to eager load
     * @return Customer|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, bool $activeOnly = false, array $relations = ["identityDocumentType"]): ?Customer {

        $query = Customer::where("id", $id)
                        ->where("company_id", $companyId);

        if($activeOnly) {

            $query->where("status", "active");

        }

        if(!empty($relations)) {

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

        $query = Customer::where("company_id", $companyId)
                         ->with(["identityDocumentType"]);

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

    /**
     * Check if document number exists in company
     *
     * @param string $documentNumber Document number
     * @param int $companyId Company
     * @param int|null $excludeId ID to exclude from check (useful for update)
     * @return bool
     */
    private static function documentNumberExists(string $documentNumber, int $companyId, ?int $excludeId = null): bool {

        $query = Customer::where("document_number", $documentNumber)
                        ->where("company_id", $companyId);

        if(Utilities::isDefined($excludeId)) {

            $query->where("id", "!=", $excludeId);

        }

        return $query->exists();

    }

}
