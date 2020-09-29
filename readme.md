# i18n
###### Internationalization package for web apps.

## Terminology

- **Index** : Dot notation index key
- **Delimiter** : Index delimiter (dot)
- **Dictionary** : Language translation file (php array)

## Description
Light weight, simple and performance oriented composer internationalization package that provides translation tools for web applications.
Converts **dot notation** index keys to strings in the **requested language** and logs unknown keys.
Language dictionaries are stored in **human readable/editable** php array files.

## Installation
First include the repository in your `composer.json` by:

```json
"repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/stamoulohta/i18n"
    }
  ]
```
Then require the package:

```bash
composer require stamoulohta/i18n:dev-master
```

## Configuration
Configuration is done via php **constants**. You can refer to [def/conf.php](def/conf.php) for extended documentation.
The required language and the delimiter can also be provided as arguments to method calls at run time.

- **I18N_NOTATION_DELIMITER** : Customizes the notation delimiter
- **I18N_LOG_UNKNOWN** : Whether to log unknown indices. `true` for `STDERR` and `string` filepath for file log
- **I18N_DICTIONARY_PATH** : Path to dictionary files
- **I18N_LANGUAGE_CODE** : Requested language. It can also be provided in instantiation as a variable

## Usage
After instantiation use either `get()` or `echo()` methods to **retrieve** or **print** the translated stings respectively.

```php
use Stamoulohta\i18n\Translator;

$translator = Translator::get_instance($language);

echo $translator->get('buttons.submit');
$translator->echo('buttons.submit');
```

## Script
Optionally, you can include the `i18n` script in your `composer.json` for command line testing.

```json
"scripts": {
    "i18n": ["vendor/bin/i18n.php"]
  }
```
Then you can test your configuration in the command line.

```bash
composer i18n -- --language en --path res/lang --delimiter _ buttons.submit buttons.cancel
composer i18n -- -l nl -p res/lang buttons.submit buttons.cancel
composer i18n buttons.submit buttons.cancel
```
The script loads the generated `autoload.php` so if you want it to respect the configuration you can include it in `composer.json`.

```json
"autoload-dev": {
    "files": [
      "def/conf.php"
    ]
  }
```

## Contact
- **George Stamoulis** via [mail](mailto:g.a.stamoulis@gmail.com) or by visiting his [webpage](http://stamoulohta.com).