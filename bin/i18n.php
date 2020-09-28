#! /usr/bin/env php
<?php
// Don't expose anything to the outside.
if (@PHP_SAPI !== 'cli') {
    die;
}
// Don't repeat warnings.
ini_set('log_errors', 0);
ini_set('display_errors', 1);

use Stamoulohta\i18n\Translator;

function includeIfExists(string $file): bool
{
    return file_exists($file) && include $file;
}

function translate($translator, $opt_index)
{
    global $argv;
    foreach (array_splice($argv, $opt_index) as $index_key) {
        $translator->echo($index_key);
        echo(PHP_EOL);
    }
}

function run()
{
    $opt_index = null;
    $options = getopt('p:l:D:', ['path:', 'language:', 'delimiter:'], $opt_index); // lowercase 'd' short option for delimiter bugs out!

    $language = $options['l'] ?? $options['language'] ?? (defined('I18N_LANGUAGE_CODE') ? I18N_LANGUAGE_CODE : '0');
    $delimiter = $options['D'] ?? $options['delimiter'] ?? null;
    $path = $options['p'] ?? $options['path'] ?? null;

    $translator = Translator::get_instance($language);
    if (! empty($delimiter)) {
        $translator->set_delimiter($delimiter);
    }

    if (! empty($path)) {
        $translator->set_path($path);
    }

    translate($translator, $opt_index);
}

if (
    ! includeIfExists(dirname(__DIR__, 1) . '/vendor/autoload.php') &&  // locally [/bin]
    ! includeIfExists(dirname(__DIR__, 1) . '/autoload.php') &&  // dependency [/vendor/bin]
    ! includeIfExists(dirname(__DIR__, 3) . '/autoload.php')            // dependency [/vendor/Stamoulohta/i18n]
) {
    fwrite(STDERR, 'Install dependencies using Composer.' . PHP_EOL);
    exit(1);
}

run();