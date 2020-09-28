<?php

namespace Stamoulohta\i18n;

class Logger
{
    /**
     * Message format for {@link E_USER_WARNING}.
     */
    const WARNING_MESSAGE = 'Translation missing for "%s" in "%s.php"';

    /**
     * Appended to the unknown language indices log filename.
     */
    const LOG_FILE_APPEND = '_unknown.lst';

    /**
     * @var Logger Singleton class instance.
     */
    private static $instance;

    /**
     * @var string Path for unknown language indices files.
     */
    private $log_path;

    /**
     * @var array Cached unknown indices.
     */
    private $unknown;

    /**
     * Singleton provider.
     *
     * @return Logger Singleton class instance.
     */
    public static function get_instance()
    {
        if (! self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Private constructor to ensure singleton.
     */
    private function __construct()
    {
        if (defined('I18N_LOG_UNKNOWN') && is_string(I18N_LOG_UNKNOWN)) {
            $this->log_path = rtrim(I18N_LOG_UNKNOWN, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if(! is_dir($this->log_path)) {
                mkdir($this->log_path, 0775, true);
            }
        }
    }

    /**
     * Prints any new {@link Logger::$unknown, unknown language indices} to file if {@link I18N_LOG_UNKNOWN} is a {@link Logger::$log_path, filepath}.
     */
    public function __destruct()
    {
        if(empty($this->unknown)) {
            return;
        }
        foreach($this->unknown as $lang => $indices) {
            $filename = $this->log_path . $lang . self::LOG_FILE_APPEND;
            $registered_indices = @file($filename, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) ?: [];
            $unregistered_indices = array_diff($indices, $registered_indices);
            file_put_contents($filename, implode(PHP_EOL, $unregistered_indices) . PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * Either caches or triggers an {@link E_USER_WARNING} for the unknown index.
     *
     * @param $lang string The language missing the index.
     * @param $index string The unknown index.
     */
    public function unknown($lang, $index)
    {
        if($this->log_path) {
            $this->cache($lang, $index);
        } else {
            $this->warn($lang, $index);
        }
    }

    /**
     * Triggers a {@link E_USER_WARNING} for {@link Logger::WARNING_MESSAGE} with the given parameters.
     *
     * @param $lang string The language missing the index.
     * @param $index string The unknown index.
     */
    private function warn($lang, $index)
    {
        $msg = sprintf(self::WARNING_MESSAGE, $index, $lang);
        trigger_error($msg, E_USER_WARNING);
    }

    /**
     * Caches the unknown index if it isn't a duplicate.
     *
     * @param $lang string The language missing the index.
     * @param $index string The unknown index.
     */
    private function cache($lang, $index)
    {
        if (! in_array($index, $this->unknown[$lang] ?? [])) {
            $this->unknown[$lang][] = $index;
        }
    }
}