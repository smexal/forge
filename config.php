<?php

// FIND SERVER ROOT PATH EXTENSION
$root = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
$dir = str_replace("\\", "/", dirname($_SERVER['SCRIPT_NAME']));
$ext = str_replace($root, '', $dir);
if(substr($ext, strlen($ext)-1) != '/') {
  $ext.="/";
}

// GETTING PLACES
define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT'].$ext);
define('MOD_ROOT', DOC_ROOT."modules/");
define('THM_ROOT', DOC_ROOT."themes/");
define('WWW_ROOT', $ext);
define('UPLOAD_WWW', WWW_ROOT."uploads/");
define('UPLOAD_DIR', DOC_ROOT."uploads/");
define('CORE_ROOT', DOC_ROOT."core/");
define('CORE_WWW_ROOT', $ext."core/");
define('CORE_TEMPLATE_DIR', CORE_ROOT."ressources/templates/");
define('FOOTPRINT_SIZE', 10);

// DEVELOPMENTZ
define('DEVELOPMENT', true);
define('LOG_LEVEL', 'DEBUG');
define('LOG_TRACE_LINES', 10);

// LANGUAGE DEFINTIONS
define('DEFAULT_LANGUAGE', 'de');
define('AVAILABLE_LANGUAGES', 'de,en');

// DATABAZZE
define('DB_HOST', "localhost");
define('DB_USER', "root");
define('DB_PASSWORD', "root");
define('DB_NAME', "butterlan");

// SEGURIDDY
define('SECURE', false);
define('CACHE_SALT', 'aöpu2¨0 56p-!?\'3zn\\5hap0o h');
define('AUTOLOADER_CLASS_FLUSH', false);
define('MANAGER_CACHE_FLUSH', true);

date_default_timezone_set("Europe/Zurich");

/* SOOO MANY BACKSLASHES! */
define('AUTOLOAD_PLACES', '(Core\\\\|Theme\\\\[^\\\\]+\\\\|Modules\\\\[^\\\\]+\\\\)');
/* Checkout AutoLoader::AddPaths() */
define('AUTOLOAD_PATHS', serialize([
    'forge_doc_views' => ['/^Forge\\\\Views.*$/',   DOC_ROOT],
    'forge_core'      => ['/^Forge\\\\Core.*$/',    CORE_ROOT],
    'forge_themes'    => ['/^Forge\\\\Themes.*$/',  THM_ROOT],
    'forge_modules'   => ['/^Forge\\\\Modules.*$/', MOD_ROOT]
]));

// Checkout AutoLoader::addMapping();
define('AUTOLOAD_MAPPING', serialize([
    'forge_app'        => ['/^Forge\\\\Core\\\\(App\\\\(.*))$/',                  '$1', '/^(.*)$/',  'class.$1.php'],
    'forge_components' => ['/^Forge\\\\' . AUTOLOAD_PLACES . 'Components(.*)$/',  '$2', '/^(.*)$/',  'class.$1.php'],
    'forge_interfaces' => ['/^Forge\\\\' . AUTOLOAD_PLACES . '(Interfaces.*)$/',  '$2', '/^I(.*)$/', 'interface.$1.php'],
    'forge_traits'     => ['/^Forge\\\\' . AUTOLOAD_PLACES . '(Traits.*+)$/',     '$2', '/^(.*)$/',  'trait.$1.php'],
    'forge_views'      => ['/^Forge\\\\' . AUTOLOAD_PLACES . '(Views.*+)$/',      '$2', '/^(.*)View$/',  'view.$1.php'],
    //'forge_temes'      => ['/^Forge\\\\Themes(Views.*+)$/',      '$2', '/^(.*)View$/',  'view.$1.php'],
    'forge_base_views' => ['/^Forge\\\\(Views\\\\.*+)$/',                         '$1', '/^(.*)View$/',  'view.$1.php'],
    'forge_default'    => ['/^Forge\\\\' . AUTOLOAD_PLACES . '(.+)$/',            '$2', '/^(.*)$/',  'class.$1.php']
]));