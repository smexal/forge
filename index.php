<?php

include("config.php");
include("core/loader.php");

$loader = Forge\Loader::instance();

use Forge\Core\App as App;

App\Auth::session();
// all php loaded; instance the app
$app = App\App::instance();

// run and output
$app->run();


?>
