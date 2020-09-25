<?php

namespace Stamoulohta\i18n;

class Translator
{
    static private $log = true;
    static private $path;
    static private $lang;
    static private $delimiter = '.';

    static private $dict;

    public static function init($lang = null, $path = null, $log = null, $delimiter = null)
    {
        self::set_log($log);
        self::set_lang($lang);
        self::set_path($path);
        self::set_delimiter($delimiter);
    }

    public static function set_log($log = null)
    {
        self::$log = $log ?? defined('I18N_ERROR_LOG') ? I18N_ERROR_LOG : false;
    }

    public static function set_delimiter($delimiter = null)
    {
        self::$delimiter = $delimiter ?: defined('I18N_NOTATION_DELIMITER') ? I18N_NOTATION_DELIMITER : self::$delimiter;
    }

    public static function set_lang($lang = null)
    {
        self::$lang = $lang ?: (defined('I18N_LANGUAGE_CODE') ? I18N_LANGUAGE_CODE : 'en');
        if (! empty(self::$path)) {
            self::set_dict();
        }
    }

    public static function set_path($path = null)
    {
        if (empty($path)) {
            $path = defined('I18N_DICTIONARY_PATH') ? I18N_DICTIONARY_PATH : '.';
        }
        self::$path = rtrim($path, DIRECTORY_SEPARATOR);

        if (! empty(self::$lang)) {
            self::set_dict();
        }
    }

    private static function set_dict()
    {
        self::$dict = self::load_dict(self::$lang);
    }

    private static function load_dict($lang)
    {
        /** @noinspection PhpIncludeInspection */
        return include(sprintf('%s/%s.php', self::$path, $lang));
    }

    private static function descend(&$dict, $keys)
    {
        $var = @$dict[array_shift($keys)];
        if (is_array($var)) {
            return self::descend($var, $keys);
        }
        return $var;
    }

    public static function get($index, $lang = null, $delimiter = null)
    {
        $delimiter = $delimiter ?: self::$delimiter;
        $dict = $lang === null ? self::$dict : self::load_dict($lang);

        $val = self::descend($dict, explode($delimiter, $index));

        if (! $val && self::$log) {
            $msg = sprintf('Translation missing for "%s" in "%s/%s.php"', $index, self::$path, $lang ?: self::$lang);
            trigger_error($msg, E_USER_WARNING);
        }
        return $val ?: $index;
    }

    public static function echo($index, $lang = null, $delimiter = null)
    {
        echo(self::get($index, $lang, $delimiter));
    }
}