<?php

include("config.php");
include("core/loader.php");

$loader = Loader::instance();
Auth::session();
// all php loaded; instance the app
$app = App::instance();

// required styles
$loader->addStyle("core/css/externals/bootstrap.core.min.css", false, false);

// admin styles
$loader->addStyle("core/css/bootstrap.less", false, "manage");
$loader->addStyle("core/css/tagsinput.less", false, "manage");
$loader->addStyle("core/css/overlay.less", false, "manage");
$loader->addStyle("core/css/layout.less", false, "manage");
$loader->addStyle("core/css/elements.less", false, "manage");
$loader->addStyle("core/css/loader.less", false, "manage");
$loader->addStyle("core/css/fonts.less", false, "manage");

// run and output
$app->run();


?>
