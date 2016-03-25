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
define('WWW_ROOT', $ext);
define('CORE_ROOT', DOC_ROOT."core/");
define('CORE_WWW_ROOT', $ext."core/");
define('CORE_TEMPLATE_DIR', CORE_ROOT."templates/");
define('FOOTPRINT_SIZE', 10);

// DEVELOPMENTZ
define('LOG_LEVEL', 'DEBUG');

// LANGUAGE DEFINTIONS
define('DEFAULT_LANGUAGE', 'de');
define('AVAILABLE_LANGUAGES', 'de,en');

// DATABAZZE
define('DB_HOST', "localhost");
define('DB_USER', "root");
define('DB_PASSWORD', "");
define('DB_NAME', "forge");

define('SECURE', false);

?>
