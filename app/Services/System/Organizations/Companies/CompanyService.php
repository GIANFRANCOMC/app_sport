<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Companies;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB, Storage};
use Illuminate\Http\UploadedFile;

use App\Models\System\Organizations\{Company, CompanySocialMedia};

/**
 * Service class for managing Company operations
 * Handles business logic for updating companies
 */
class CompanyService {

    /**
     * Translation namespace for company module
     */
    private const TRANSLATION_NAMESPACE = "System.Organizations.company";

    /**
     * Allowed fields for company update
     */
    private const ALLOWED_FIELDS = [
        "identity_document_type_id",
        "document_number",
        "legal_name",
        "commercial_name",
        "tagline",
        "description",
        "address",
        "telephone",
        "email"
    ];

    /**
     * Image fields that can be uploaded
     */
    private const IMAGE_FIELDS = [
        "logotype",
        "combinationmark",
        "logomark",
        "login_image"
    ];

    /**
     * Social media types
     */
    private const SOCIAL_MEDIA_TYPES = [
        "facebook",
        "instagram",
        "whatsapp"
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
     * Handle file upload for company images
     *
     * @param Company $company Company instance
     * @param string $fieldName Field name (logotype, combinationmark, etc.)
     * @param UploadedFile|null $file Uploaded file
     * @return string|null File path or null
     */
    private static function handleImageUpload(Company $company, string $fieldName, ?UploadedFile $file): ?string {

        if(!$file || !$file->isValid()) {

            return null;

        }

        $extension = $file->getClientOriginalExtension();
        $fileName  = "{$fieldName}.{$extension}";
        $filePath  = $file->storeAs($company->internal_code, $fileName, "public");

        return $filePath;

    }

    /**
     * Update or create social media link
     *
     * @param Company $company Company instance
     * @param string $type Social media type (facebook, instagram, whatsapp)
     * @param string|null $link Social media link
     * @param int|null $userId User ID
     * @return CompanySocialMedia
     */
    private static function updateOrCreateSocialMedia(Company $company, string $type, ?string $link, ?int $userId): CompanySocialMedia {

        $socialMedia = CompanySocialMedia::where("company_id", $company->id)
                                         ->where("type", $type)
                                         ->first();

        if(Utilities::isDefined($socialMedia)) {

            $socialMedia->link       = $link ?? "";
            $socialMedia->status     = "active";
            $socialMedia->updated_at = now();
            $socialMedia->updated_by = $userId;
            $socialMedia->save();

        }else {

            $socialMedia = new CompanySocialMedia();
            $socialMedia->company_id = $company->id;
            $socialMedia->type       = $type;
            $socialMedia->link       = $link ?? "";
            $socialMedia->status     = "active";
            $socialMedia->created_at = now();
            $socialMedia->created_by = $userId;
            $socialMedia->save();

        }

        return $socialMedia;

    }

    /**
     * Prepare company data for update
     *
     * @param Company $company Company instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareCompanyDataForUpdate(Company $company, array $data): array {

        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field]) && $data[$field] !== $company->$field) {

                $updateData[$field] = $data[$field];

            }

        }

        return $updateData;

    }

    /**
     * Update an existing company
     *
     * @param Company $company Company instance to update
     * @param array $data Updated company data
     * @param array $files Uploaded files array
     * @param int|null $userId User ID updating the company
     * @return Company Updated company instance
     * @throws Exception
     */
    public static function update(Company $company, array $data, array $files = [], ?int $userId = null): Company {

        DB::transaction(function() use($company, $data, $files, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id;

            // Prepare update data with only changed fields
            $updateData = self::prepareCompanyDataForUpdate($company, $data);

            // Handle image uploads
            foreach(self::IMAGE_FIELDS as $field) {

                if(isset($files[$field]) && $files[$field] instanceof \Illuminate\Http\UploadedFile) {

                    $filePath = self::handleImageUpload($company, $field, $files[$field]);

                    if($filePath) {

                        $updateData[$field] = $filePath;

                    }

                }

            }

            // Update company
            if(!empty($updateData)) {

                $updateData["updated_at"] = now();
                $updateData["updated_by"] = $userId;
                $company->update($updateData);

            }

            // Update or create social media links
            foreach(self::SOCIAL_MEDIA_TYPES as $type) {

                if(isset($data[$type])) {

                    self::updateOrCreateSocialMedia($company, $type, $data[$type], $userId);

                }

            }

        });

        return $company->fresh(["identityDocumentType"]);

    }

}

