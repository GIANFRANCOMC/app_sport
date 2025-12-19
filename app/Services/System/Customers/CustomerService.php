<?php

declare(strict_types=1);

namespace App\Services\System\Customers;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};

use App\Models\System\Customers\Customer;
use App\Repositories\System\Customers\CustomerRepository;

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
     * @var CustomerRepository
     */
    private static $repository;

    /**
     * Get repository instance (lazy initialization)
     *
     * @return CustomerRepository
     */
    private static function getRepository(): CustomerRepository {

        if(self::$repository === null) {

            self::$repository = new CustomerRepository();

        }

        return self::$repository;

    }

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
            if(self::getRepository()->fieldExists("document_number", $data["document_number"], $companyId)) {

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

                if(self::getRepository()->fieldExists("document_number", $data["document_number"], $customer->company_id, $customer->id)) {

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
     * @param array $relations Relations to eager load
     * @return Customer|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, array $relations = ["identityDocumentType"]): ?Customer {

        return self::getRepository()->findByIdAndCompany($id, $companyId, $relations);

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

        return self::getRepository()->getPaginatedList($companyId, $filters, $perPage);

    }

}

