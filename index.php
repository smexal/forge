<?php

use \Forge\Core\App\App;
use \Forge\Core\App\Auth;

include("config.php");
include("core/loader.php");

$loader = \Forge\Loader::instance();

Auth::session();
// all php loaded; instance the app
$app = App::instance();

// run and output
$app->run();


?>
