<?php

namespace Stamoulohta\i18n;

class Lexer
{
    private static $instance;


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
     * @var bool Whether new insertions were made and {@link Lexer::$dictionary, dictionary} needs to be updated.
     */
    private $update;

    /**
     * @var Lexer[] Class singletons.
     */
    private static $instances = [];

    /**
     * Singleton provider.
     *
     * @param string $language The language requested.
     *
     * @return Lexer Singleton class instance.
     */
    public static function get_instance($language = I18N_LANGUAGE_CODE)
    {
        if (! array_key_exists($language, self::$instances)) {
            self::$instances[$language] = new static($language);
        }
        error_log($language);

        return self::$instances[$language];

    }

     /**
     * Private constructor to ensure singleton.
     *
     * @param $language string The required language.
     */
    private function __construct($language)
    {
        $this->update = false;
        $this->language = $language;

        $this->path = defined('I18N_DICTIONARY_PATH') ? I18N_DICTIONARY_PATH : Translator::DEFAULT_DICTIONARY_PATH;
        $this->path = rtrim($this->path, DIRECTORY_SEPARATOR);

        $this->delimiter = defined('I18N_NOTATION_DELIMITER') ? I18N_NOTATION_DELIMITER : Translator::DEFAULT_DELIMITER;

        $this->set_dictionary();
    }

    /**
     * Prints any new {@link Logger::$unknown, unknown language indices} to file if {@link I18N_LOG_UNKNOWN} is a {@link Logger::$log_path, filepath}.
     * Updates the dictionary file.
     */
    public function __destruct()
    {
        if(!$this->update) {
            return;
        }

        $filename = sprintf('%s/%s.php', $this->path, $this->language);
        file_put_contents($filename, '<?php return ' . var_export($this->dictionary, true) . ';');
    }

    /**
     * @param $delimiter string Sets the notation delimiter.
     */
    public function set_delimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @param $path string Sets the path and reloads the dictionary.
     */
    public function set_path($path)
    {
        if($this->path === $path) {
            return;
        }
        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
        $this->set_dictionary();
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
     * @param $path string The path to dictionary files.
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
     * Adds the given index to the {@link Lexer::$dictionary, dictionary} with with value NULL
     * and sets the update flag.
     *
     * @param $index String notation index.
     * @param $value null|String value to insert.
     */
    public function insert($index, $value = '')
    {
        $dict = &$this->dictionary;
        foreach(explode($this->delimiter, $index) as $key) {
            $dict = &$dict[$key];
        }
        $dict = $value;
        $this->update = true;
    }

    /**
     * Returns the whole instance's {@link Lexer::$dictionary, dictionary} as an one dimensional array.
     *
     * @return array One dimensional array;
     */
    public function flat() {
        return $this->flatten($this->dictionary);
    }

    /**
     * Converts the given dictionary array to one dimension and returns it.
     *
     * @param array $dictionary the array to be flatten.
     * @param String  $_context Used for recursion. <b>DO NOT SET</b>.
     *
     * @return array
     */
    private function flatten($dictionary, $_context = '')
    {
        foreach ($dictionary as $key => $value) {
            if (is_array($value)) {
                foreach ($this->flatten($value, "$_context$key{$this->delimiter}") as $iKey => $iValue) {
                    $out[$iKey] = $iValue;
                }
            } else {
                $out["$_context$key"] = $value;
            }
        }
        return $out ?? [];
    }

    private function build(&$dictionary, $notation, $value)
    {
        $keys = explode($this->delimiter, $notation, 2);

        if (count($keys) > 1) {
            $this->build($dictionary[$keys[0]], $keys[1], $value);
        } else {
            $dictionary[$keys[0]] = $value;
        }
    }

    public function toDictionary($flatten, $dictionary = []) {
        foreach($flatten as $notation => $value) {
            $this->build($dictionary, $notation, $value);
        }
        $this->dictionary = $dictionary;
        $this->update = true;
    }
}
