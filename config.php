<?php

// FIND SERVER ROOT PATH EXTENSION
$root = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
$dir = str_replace("\\", "/", __DIR__);
$ext = str_replace($root, '', $dir);
if(substr($ext, strlen($ext)-1) != '/') {
  $ext.="/";
}

// GETTING PLACES
define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT'].$ext);
define('WWW_ROOT', $ext);
define('TEMPLATE_DIR', DOC_ROOT."templates/");

// DEVELOPMENTZ
define('LOG_LEVEL', 'DEBUG');
define('FOOTPRINT_SIZE', 10);

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
