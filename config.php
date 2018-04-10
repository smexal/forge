<?php

// FIND SERVER ROOT PATH EXTENSION
$root = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
$dir = str_replace("\\", "/", dirname($_SERVER['SCRIPT_NAME']));
$ext = str_replace($root, '', $dir);
if(substr($ext, strlen($ext)-1) != '/') {
  $ext.="/";
}

if(file_exists('config-env.php')) {
    require_once('config-env.php');
}

// GETTING PLACES
@define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT'].$ext);
@define('MOD_ROOT', DOC_ROOT."modules/");
@define('THM_ROOT', DOC_ROOT."themes/");
@define('WWW_ROOT', $ext);
@define('UPLOAD_WWW', WWW_ROOT."uploads/");
@define('UPLOAD_DIR', DOC_ROOT."uploads/");
@define('CORE_ROOT', DOC_ROOT."core/");
@define('CORE_WWW_ROOT', WWW_ROOT."core/");
@define('CORE_TEMPLATE_DIR', CORE_ROOT."ressources/templates/");
@define('FOOTPRINT_SIZE', 10);

// DEVELOPMENTZ
@define('DEVELOPMENT', true);
@define('LOG_LEVEL', 'DEBUG');
@define('LOG_TRACE_LINES', 10);

// LANGUAGE DEFINTIONS
@define('DEFAULT_LANGUAGE', 'de');
@define('AVAILABLE_LANGUAGES', 'de,en');

// DATABAZZE
@define('DB_HOST', "localhost");
@define('DB_USER', "root");
@define('DB_PASSWORD', "");
@define('DB_NAME', "butterlan");

// SEGURIDDY
@define('SECURE', false);
@define('CACHE_SALT', 'aöpu2¨0 56p-!?\'3zn\\5hap0o h');
@define('AUTOLOADER_CLASS_FLUSH', false || isset($_GET['flushac']) || isset($_GET['flushall']));
@define('MANAGER_CACHE_FLUSH', false || isset($_GET['flushmc']) || isset($_GET['flushall']));

@define('HASH_SALT_1', 'kl34j%%');
@define('HASH_SALT_2', 'dkfje/liker=0=');

@define('USE_CACHE', true);

/*
    Caching time for HTML Sites without login
 */
@define('CACHE_TIME', 5);
@define('MINIFY', true);

date_default_timezone_set("Europe/Zurich");
