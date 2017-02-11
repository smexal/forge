<?php
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\AutoLoader as AutoLoader;

include("config.php");
include("core/autoloader.php");
include("core/loader.php");

// AutoLoader::$DEBUG = AutoLoader::DEBUG_PAGE;
AutoLoader::addPaths(unserialize(AUTOLOAD_PATHS));
AutoLoader::addMapping(unserialize(AUTOLOAD_MAPPING));

$loader = \Forge\Loader::instance();

Auth::session();
// all php loaded; instance the app
$app = App::instance();

// run and output
$app->run();
