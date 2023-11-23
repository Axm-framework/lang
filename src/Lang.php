<?php

namespace Axm\Lang;

use Axm\Exception\AxmException;

/**
 * Class Lang
 * 
 * @author Juan Cristobal <juancristobalgd1@gmail.com>
 * @link http://www.axm.com/
 * @license http://www.axm.com/license/
 * @package Axm
 * 
 * Interface defining the methods required for a language translator.
 */
interface LangInterface
{
    /**
     * Get the current locale.
     *
     * @return string The current locale.
     */
    public function getLocale(): string;

    /**
     * Translate a key with optional parameters.
     *
     * @param string $key The translation key.
     * @param array $params Optional parameters for string interpolation.
     * @return string The translated message.
     */
    public function trans(string $key, array $params = []): string;
}

/**
 * Class implementing Lang for handling language localization.
 */
class Lang implements LangInterface
{
    private static $instance;
    private $translations = [];
    private $locale;
    const DEFAULT_LANGUAGE = 'en_EN';

    /**
     * Private constructor to enforce singleton pattern and load translations.
     */
    private function __construct()
    {
        $this->setLocale();
    }

    /**
     * Get an instance of the Lang class.
     *
     * @return LangInterface An instance of the Lang class.
     */
    public static function make(): LangInterface
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set the current locale and reload translations.
     */
    public function setLocale(): void
    {
        $this->locale = app()->getLocale() ?? self::DEFAULT_LANGUAGE;

        // Reload translations when changing the language
        $this->loadTranslationsFromFile();
    }

    /**
     * Get the current locale.
     *
     * @return string The current locale.
     */
    public function getLocale(): string
    {
        return $this->locale ?? self::DEFAULT_LANGUAGE;
    }

    /**
     * Translate a key with optional parameters.
     *
     * @param string $key The translation key.
     * @param array $params Optional parameters for string interpolation.
     * @return string The translated message.
     */
    public function trans(string $key, array $params = []): string
    {
        list($file, $messageKey) = explode('.', $key, 2);

        $translationKeyFile = $this->getLocale() . DIRECTORY_SEPARATOR . $file;
        $translationKey = $this->translations[$translationKeyFile] ?? $key;
        $message = $translationKey[$messageKey];

        if (!empty($params)) {
            $message = vsprintf($message, $params);
        }

        return $message ?? '';
    }

    /**
     * Load translations from language files.
     *
     * @throws AxmException If an error occurs while loading language files.
     */
    public function loadTranslationsFromFile(): void
    {
        $langKey = $this->getLocale();
        $langDir = config('pahs.langPath') . DIRECTORY_SEPARATOR . $langKey . DIRECTORY_SEPARATOR;

        $this->translations = [];
        try {
            foreach (glob($langDir . '*.php') as $file) {
                $fileKey = pathinfo($file, PATHINFO_FILENAME);
                $this->translations[$langKey . DIRECTORY_SEPARATOR . $fileKey] = require $file;
            }
        } catch (\Exception $e) {
            throw new AxmException("Error loading language file: {$e->getMessage()}");
        }
    }
}
