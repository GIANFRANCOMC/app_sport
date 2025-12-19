<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Users;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};

use App\Models\System\Organizations\User;
use App\Repositories\System\Organizations\Users\UserRepository;

/**
 * Service class for managing User operations
 * Handles business logic for creating and updating users
 */
class UserService {

    /**
     * Translation namespace for user module
     */
    private const TRANSLATION_NAMESPACE = "System.Organizations.user";

    /**
     * @var UserRepository
     */
    private static $repository;

    /**
     * Get repository instance (lazy initialization)
     *
     * @return UserRepository
     */
    private static function getRepository(): UserRepository {

        if(self::$repository === null) {

            self::$repository = new UserRepository();

        }

        return self::$repository;

    }

    /**
     * Allowed fields for user creation and update
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
     * Prepare user data for creation
     *
     * @param array $data Input data
     * @param int $companyId Company ID
     * @param int $userId User ID
     * @return array
     */
    private static function prepareUserDataForCreate(array $data, int $companyId, int $userId): array {

        $userData = [
            "company_id" => $companyId,
            "status"     => $data["status"] ?? "active",
            "gender"     => $data["gender"] ?? "other",
            "created_at" => now(),
            "created_by" => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {

            if($field === "status" || $field === "gender") continue; // Already set

            $userData[$field] = $data[$field] ?? null;

        }

        // Handle password separately (it's hashed automatically by Laravel)
        if(isset($data["password"])) {

            $userData["password"] = $data["password"];

        }

        return $userData;

    }

    /**
     * Prepare user data for update (only changed fields)
     *
     * @param User $user User instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareUserDataForUpdate(User $user, array $data): array {

        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field]) && $data[$field] !== $user->$field) {

                $updateData[$field] = $data[$field];

            }

        }

        // Handle gender default
        if(isset($data["gender"])) {

            $updateData["gender"] = $data["gender"] ?? "other";

        }

        // Handle password separately (only if provided)
        if(isset($data["password"]) && !empty($data["password"])) {

            $updateData["password"] = $data["password"];

        }

        return $updateData;

    }

    /**
     * Check if document number exists
     *
     * @param string $documentNumber Document number
     * @param int $companyId Company ID
     * @param int|null $excludeUserId User ID to exclude from check
     * @return bool
     */
    private static function documentNumberExists(string $documentNumber, int $companyId, ?int $excludeUserId = null): bool {

        return self::getRepository()->fieldExists("document_number", $documentNumber, $companyId, $excludeUserId);

    }

    /**
     * Check if email exists (global check, not per company)
     *
     * @param string $email Email address
     * @param int|null $excludeUserId User ID to exclude from check
     * @return bool
     */
    private static function emailExists(string $email, ?int $excludeUserId = null): bool {

        $query = User::where("email", $email);

        if($excludeUserId) {

            $query->where("id", "!=", $excludeUserId);

        }

        return $query->exists();

    }

    /**
     * Create a new user
     *
     * @param array $data User data from request
     * @param int|null $userId User ID creating the user
     * @return User|null Created user instance or null on failure
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

            $userId = $userId ?? $userAuth->id;

            // Check if document number exists
            if(self::documentNumberExists($data["document_number"], $companyId)) {

                throw new Exception("El número de documento ingresado ya ha sido registrado.");

            }

            // Check if email exists
            if(self::emailExists($data["email"])) {

                throw new Exception("El correo electrónico ingresado ya ha sido registrado.");

            }

            // Prepare user data with only allowed fields
            $userData = self::prepareUserDataForCreate($data, $companyId, $userId);

            // Create the user
            $user = User::create($userData);

        });

        return $user;

    }

    /**
     * Update an existing user
     *
     * @param User $user User instance to update
     * @param array $data Updated user data
     * @param int|null $userId User ID updating the user
     * @return User Updated user instance
     * @throws Exception
     */
    public static function update(User $user, array $data, ?int $userId = null): User {

        DB::transaction(function() use($user, $data, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id;

            // Check if document number exists (excluding current user)
            if(isset($data["document_number"])) {

                if(self::documentNumberExists($data["document_number"], $user->company_id, $user->id)) {

                    throw new Exception("El número de documento ingresado ya ha sido registrado.");

                }

            }

            // Check if email exists (excluding current user)
            if(isset($data["email"])) {

                if(self::emailExists($data["email"], $user->id)) {

                    throw new Exception("El correo electrónico ingresado ya ha sido registrado.");

                }

            }

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
     * Find user by ID and company ID
     *
     * @param int $id User ID
     * @param int $companyId Company ID
     * @param array $relations Relations to eager load
     * @return User|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, array $relations = ["identityDocumentType", "role"]): ?User {

        return self::getRepository()->findByIdAndCompany($id, $companyId, $relations);

    }

    /**
     * Get paginated list of users
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

