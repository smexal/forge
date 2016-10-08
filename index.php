<?php

include("config.php");
include("core/loader.php");

$loader = Loader::instance();

Auth::session();
// all php loaded; instance the app
$app = App::instance();

// run and output
$app->run();


?>
