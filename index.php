<?php

include("config.php");
include("loader.php");

$loader = Loader::instance();

Auth::session();

// load external ressources
$loader->addRessource("raintpl/rain.tpl.class.php");
$loader->addRessource("helpers/additional_functions.php");
$loader->addRessource("mysqlidb/mysqlidb.php");
$loader->addRessource("lessc/lessc.inc.php");

// all php loaded; instance the app
$app = App::instance();

// all Styles
$loader->addStyle("//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css", true);
$loader->addStyle("css/bootstrap.less");
$loader->addStyle("css/layout.less");
$loader->addStyle("css/elements.less");
$loader->addStyle("css/loader.less");

// all Scripts
$loader->addScript("//code.jquery.com/jquery-1.11.2.min.js", true);
$loader->addScript("//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js", true);
$loader->addScript("scripts/helpers.js");
$loader->addScript("scripts/ajaxlinks.js");
$loader->addScript("scripts/forms.js");
$loader->addScript("scripts/overlay.js");

// run and output
$app->run();


?>