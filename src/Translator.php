<?php

namespace Stamoulohta\i18n;

/**
 * Static class Translator.
 *
 * Translates key strings denoted with {@link Translator::$delimiter, delimiter}
 * to the required {@link Translator::$language, language}.
 *
 * @package Stamoulohta\i18n
 * @see     Translator::get() Returns the translation.
 * @see     Translator::echo() Prints the translation.
 */
class Translator
{
    const DEFAULT_DICTIONARY_PATH = __DIR__ . '/../res/lang';
    const DEFAULT_DELIMITER = '.';

    /**
     * @var string Path to the language files.
     */
    private $path;

    /**
     * @var string Filename of language dictionary without ".php" extension.
     */
    private $language;

    /**
     * @var string Index notation delimiter. Default {@link Translator::DEFAULT_DELIMITER, '.' (dot)}.
     */
    private $delimiter;

    /**
     * @var array The language array.
     */
    private $dictionary;

    /**
     * @var Lexer Inserts unknown notations to dictionaries.
     * @see I18N_FILL_UNKNOWN
     */
    private $lexer;

    /**
     * @var Logger Logs unknown key notations to either STDERR or to given file.
     * @see I18N_LOG_UNKNOWN
     */
    private static $logger;

    /**
     * @var Translator[] Class singletons.
     */
    private static $instances = [];

    /**
     * Singleton provider.
     *
     * @param string $language The language requested.
     *
     * @return Translator Singleton class instance.
     */
    public static function get_instance($language = I18N_LANGUAGE_CODE)
    {
        if (! array_key_exists($language, self::$instances)) {
            self::$instances[$language] = new static($language);
        }

        return self::$instances[$language];
    }

    /**
     * Private constructor to ensure singleton.
     *
     * @param $language string The required language.
     */
    private function __construct($language)
    {
        $this->language = $language;

        $this->path = defined('I18N_DICTIONARY_PATH') ? I18N_DICTIONARY_PATH : self::DEFAULT_DICTIONARY_PATH;
        $this->path = rtrim($this->path, DIRECTORY_SEPARATOR);

        $this->delimiter = defined('I18N_NOTATION_DELIMITER') ? I18N_NOTATION_DELIMITER : self::DEFAULT_DELIMITER;

        $this->set_dictionary();

        if (defined('I18N_FILL_UNKNOWN') && I18N_FILL_UNKNOWN) {
            $this->lexer = $this->get_lexer($this->language);
        }

        if (defined('I18N_LOG_UNKNOWN') && I18N_LOG_UNKNOWN) {
            self::$logger = Logger::get_instance();
        }
    }

    private function get_lexer($language)
    {
        $lexer = Lexer::get_instance($language);
        // TODO: This seems redundant
        $lexer->set_path($this->path);
        $lexer->set_delimiter($this->delimiter);

        return $lexer;
    }

    /**
     * @param $delimiter string Sets the notation delimiter.
     */
    public function set_delimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        if ($this->lexer) {
            $this->lexer->set_delimiter($this->delimiter);
        }
    }

    /**
     * @param $path string Sets the path and reloads the dictionary.
     */
    public function set_path($path)
    {
        if ($this->path === $path) {
            return;
        }
        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
        $this->set_dictionary();
        if ($this->lexer) {
            $this->lexer->set_path($this->path);
        }
    }

    /**
     * Sets the dictionary determined by {@link Translator::$path, path} and {@link Translator::$language, language}.
     */
    private function set_dictionary()
    {
        $this->dictionary = self::load_dictionary($this->path, $this->language);
    }

    /**
     * Loads the requested dictionary from file.
     *
     * @param $path     string The path to dictionary files.
     * @param $language string The language.
     *
     * @return false|array The resulting dictionary of false.
     */
    private static function load_dictionary($path, $language)
    {
        /** @noinspection PhpIncludeInspection */
        return include(sprintf('%s/%s.php', $path, $language));
    }

    /**
     * Recursively descends in to the dictionary array looking for the notation index.
     *
     * @param $dictionary array The dictionary array.
     * @param $keys       array The keys.
     *
     * @return null|string The value if is found or NULL.
     */
    private static function descend(&$dictionary, $keys)
    {
        $var = @$dictionary[array_shift($keys)];
        if (is_array($var)) {
            return self::descend($var, $keys);
        }
        return $var;
    }

    /**
     * Returns the value of the given index or the index itself if it is unknown.
     *
     * @param $index     string The index notation.
     * @param $language  null|string The requested language. Default {@link Translator::$language}.
     * @param $delimiter null|string The requested delimiter. Default {@link Translator::$delimiter}.
     *
     * @return string The value found or the given index.
     */
    public function get($index, $language = null, $delimiter = null)
    {
        $current_dictionary = $language === null ? $this->dictionary : self::load_dictionary($this->path, $language);
        $delimiter = $delimiter ?: $this->delimiter;

        $translation = self::descend($current_dictionary, explode($delimiter, $index));

        if (! $translation) {
            if (self::$logger) {
                self::$logger->unknown($language ?: $this->language, $index);
            }
            if ($translation === null && $this->lexer) {
                $this->lexer->insert($index);
            }
        }
        return $translation ?: $index;
    }

    /**
     * Prints the value of the given index or the index itself if it is unknown.
     *
     * @param $index     string The index notation.
     * @param $language  null|string The requested language. Default {@link Translator::$language}.
     * @param $delimiter null|string The requested delimiter. Default {@link Translator::$delimiter}.
     */
    public function echo($index, $language = null, $delimiter = null)
    {
        echo($this->get($index, $language, $delimiter));
    }
}
