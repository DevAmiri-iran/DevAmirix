<?php

namespace App\Support;

class Translator {
    private static array $translations = [];
    private static string $locale;

    /**
     * Initializes the translator by setting the locale and loading the translations.
     */
    public static function init(): void
    {
        self::$locale = Config::get('app.locale', 'en');
        self::loadTranslations(self::$locale);
    }

    /**
     * Loads the translations for the specified locale.
     *
     * @param string $locale The locale to load translations for.
     */
    private static function loadTranslations(string $locale): void
    {
        $langDir = resources_path("lang\\$locale");
        if (is_dir($langDir)) {
            $files = scandir($langDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $translations = require "$langDir/$file";
                    self::$translations = array_merge(self::$translations, $translations);
                }
            }
        }
    }

    /**
     * Translates the given key.
     *
     * @param string $key The key to translate.
     * @param string|null $default The default value to return if the key is not found.
     * @return string The translated value or the default value if not found.
     */
    public static function trans(string $key, string $default = null): string
    {
        return self::$translations[$key] ?? $default ?? $key;
    }
}