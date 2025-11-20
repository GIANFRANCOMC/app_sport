<?php

declare(strict_types=1);

namespace App\Helpers\System;

use Illuminate\Support\Facades\App;

/**
 * Translation Helper
 * Provides centralized translation management with entity-based organization
 */
class TranslationHelper {

    /**
     * Get translation for a specific entity and key
     *
     * @param string $entity Entity name (e.g., 'System.Organizations.branch', 'branch')
     * @param string $key Translation key
     * @param array $replace Replacements for placeholders
     * @param string|null $locale Locale override (defaults to current locale)
     * @return string
     */
    public static function get(string $entity, string $key, array $replace = [], ?string $locale = null): string {

        $locale = $locale ?? App::getLocale();

        // Convert dots to slashes for nested directories
        $filePath = str_replace(".", "/", $entity);
        $translationKey = "{$filePath}.{$key}";

        return trans($translationKey, $replace, $locale);

    }

    /**
     * Get translation with fallback to English if not found
     *
     * @param string $entity Entity name (e.g., 'System.Organizations.branch')
     * @param string $key Translation key
     * @param array $replace Replacements
     * @param string|null $locale Locale override
     * @return string
     */
    public static function getWithFallback(string $entity, string $key, array $replace = [], ?string $locale = null): string {

        $locale = $locale ?? App::getLocale();

        // Convert dots to slashes for nested directories
        $filePath = str_replace(".", "/", $entity);
        $translationKey = "{$filePath}.{$key}";

        // Try current locale first
        $translation = trans($translationKey, $replace, $locale);

        // If translation not found or same as key, try English fallback
        if($translation === $translationKey && $locale !== "en") {

            $translation = trans($translationKey, $replace, "en");

        }

        return $translation;

    }

    /**
     * Get all translations for an entity
     *
     * @param string $entity Entity name (e.g., 'System.Organizations.branch')
     * @param string|null $locale Locale override
     * @return array
     */
    public static function getAll(string $entity, ?string $locale = null): array {

        $locale = $locale ?? App::getLocale();

        // Convert dots to slashes for nested directories
        $filePath = str_replace(".", "/", $entity);

        return trans("{$filePath}", [], $locale);

    }

    /**
     * Check if translation exists
     *
     * @param string $entity Entity name (e.g., 'System.Organizations.branch')
     * @param string $key Translation key
     * @param string|null $locale Locale override
     * @return bool
     */
    public static function exists(string $entity, string $key, ?string $locale = null): bool {

        $locale = $locale ?? App::getLocale();

        // Convert dots to slashes for nested directories
        $filePath = str_replace(".", "/", $entity);
        $translationKey = "{$filePath}.{$key}";
        $translation = trans($translationKey, [], $locale);

        return $translation !== $translationKey;

    }

}
