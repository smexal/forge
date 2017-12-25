<?php

if(array_key_exists('debug', $_GET)) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}


use \Forge\Core\Classes\Logger;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\SuperLoader as SuperLoader;

include("config.php");
include("core/superloader.php");
include("core/loader.php");

SuperLoader::$DEBUG = SuperLoader::DEBUG_LOG;
SuperLoader::$BASE_DIR = DOC_ROOT;
SuperLoader::$FLUSH = AUTOLOADER_CLASS_FLUSH === true;

spl_autoload_register(array(SuperLoader::instance(), "autoloadClass"));
$loader = \Forge\Loader::instance();
Auth::session();
// all php loaded; instance the app
$app = App::instance();

// run and output
$app->run();
