#! /usr/bin/env php
<?php
// Don't expose anything to the outside.
if (@PHP_SAPI !== 'cli') {
    die;
}
ini_set('log_errors', 0);
ini_set('display_errors', 1);

include_once(dirname(__DIR__). '/vendor/autoload.php');

use Stamoulohta\i18n\Translator;

Translator::init();
foreach (array_slice($argv, 1) as $key) {
    Translator::echo($key);
    echo("\n");
}