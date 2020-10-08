<?php

/**
 * The delimiter to be used for the <b>index</b> notation.
 *
 * Optional, default {@link \Stamoulohta\i18n\Translator::DEFAULT_DELIMITER, '.' (dot)}.
 */
const I18N_NOTATION_DELIMITER = '.';

/**
 * Whether to log unknown <b>indices</b>.
 *
 * Optional, true for std_err and filepath to print to a file. Default is false.
 */
const I18N_LOG_UNKNOWN = true;

/**
 * Path to the dictionary files.
 *
 * Optional, default {@link \Stamoulohta\i18n\Translator::DEFAULT_DICTIONARY_PATH, "res/lang"}.
 */
define('I18N_DICTIONARY_PATH', dirname(__FILE__, 2) . '/res/lang');

/**
 * Filename of language dictionary without ".php" extension.
 *
 * Must be defined for {@link \Stamoulohta\i18n\Translator::get_instance(), Translator::get_instance()} defaults to this.
 */
const I18N_LANGUAGE_CODE = 'en';


/**
 * Whether to fill dictionary with unknown <b>indices</b>.
 *
 * Optional, true to fill with NULL;
 */
const I18N_FILL_UNKNOWN = true;