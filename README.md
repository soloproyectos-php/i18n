# i18n
A simple internationalization library.

## Installation

Open a terminal and execute the following command from your directory:
```bash
composer require soloproyectos-php/i18n
```

## Introduction

Dictionaries are JSON files and must be located under the same directory. For example:

```plaintext
./i18n/en.json
./i18n/es.json
./i18n/<language>.json
```

## Example

Loads dictionaries and use a specific language
```php
header("Content-Type: text/plain; charset=utf-8");
require_once "../vendor/autoload.php";
use soloproyectos\i18n\translator\I18nTranslator;

$t = new I18nTranslator();

// loads dictionaries from the ./i18n directory
// and sets the default language to 'es'
$t->loadDictionaries("./i18n", "es");

// use the 'en' language
$t->useLang("en");
```
