<?php

declare(strict_types=1);

namespace App\Services\System\Customers;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};

use App\Models\System\Customers\Customer;

/**
 * Service class for managing Customer operations
 * Handles business logic for creating and updating customers
 */
class CustomerService {

    /**
     * Translation namespace for customer module
     */
    private const TRANSLATION_NAMESPACE = "System.Customers.customer";

    /**
     * Allowed fields for customer creation and update
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
     * Prepare customer data for creation
     *
     * @param array $data Input data
     * @param int $companyId Company ID
     * @param int $userId User ID
     * @return array
     */
    private static function prepareCustomerDataForCreate(array $data, int $companyId, int $userId): array {

        $customerData = [
            "company_id" => $companyId,
            "status"     => $data["status"] ?? "active",
            "gender"     => $data["gender"] ?? "other",
            "created_at" => now(),
            "created_by" => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {

            if($field === "status" || $field === "gender") continue; // Already set

            $customerData[$field] = $data[$field] ?? null;

        }

        return $customerData;

    }

    /**
     * Prepare customer data for update (only changed fields)
     *
     * @param Customer $customer Customer instance
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

        // Handle gender default
        if(isset($data["gender"])) {

            $updateData["gender"] = $data["gender"] ?? "other";

        }

        return $updateData;

    }

    /**
     * Create a new customer
     *
     * @param array $data Customer data from request
     * @param int|null $userId User ID creating the customer
     * @return Customer|null Created customer instance or null on failure
     * @throws \Exception
     */
    public static function create(array $data, ?int $userId = null): ?Customer {

        $customer = null;

        DB::transaction(function() use($data, $userId, &$customer) {

            $userAuth  = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {

                throw new Exception(self::trans("company_id_required"));

            }

            $userId = $userId ?? $userAuth->id;

            // Check if document number exists
            $customerExists = Customer::where("company_id", $companyId)
                                      ->where("document_number", $data["document_number"])
                                      ->exists();

            if($customerExists) {

                throw new Exception(self::trans("document_number_exists"));

            }

            // Prepare customer data with only allowed fields
            $customerData = self::prepareCustomerDataForCreate($data, $companyId, $userId);

            // Create the customer
            $customer = Customer::create($customerData);

        });

        return $customer;

    }

    /**
     * Update an existing customer
     *
     * @param Customer $customer Customer instance to update
     * @param array $data Updated customer data
     * @param int|null $userId User ID updating the customer
     * @return Customer Updated customer instance
     * @throws \Exception
     */
    public static function update(Customer $customer, array $data, ?int $userId = null): Customer {

        DB::transaction(function() use($customer, $data, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id;

            // Check if document number exists (excluding current customer)
            if(isset($data["document_number"])) {

                $customerExists = Customer::where("company_id", $customer->company_id)
                                          ->where("document_number", $data["document_number"])
                                          ->where("id", "!=", $customer->id)
                                          ->exists();

                if($customerExists) {

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
     * Find customer by ID and company ID
     *
     * @param int $id Customer ID
     * @param int $companyId Company ID
     * @return Customer|null
     */
    public static function findByIdAndCompany(int $id, int $companyId): ?Customer {

        return Customer::where("id", $id)
                       ->where("company_id", $companyId)
                       ->first();

    }

    /**
     * Get paginated list of customers
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15) {

        $query = Customer::where("company_id", $companyId)
                         ->with(["identityDocumentType"]);

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
        $searchableFields = ["document_number", "name", "email", "phone_number"];

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

}

