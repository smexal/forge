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

// required styles
$loader->addStyle("//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css", true);

// admin styles
$loader->addStyle("css/bootstrap.less", false, "manage");
$loader->addStyle("css/tagsinput.less", false, "manage");
$loader->addStyle("css/overlay.less", false, "manage");
$loader->addStyle("css/layout.less", false, "manage");
$loader->addStyle("css/elements.less", false, "manage");
$loader->addStyle("css/loader.less", false, "manage");
$loader->addStyle("css/fonts.less", false, "manage");

// all Scripts
$loader->addScript("//code.jquery.com/jquery-1.11.2.min.js", true);
$loader->addScript("//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js", true);
$loader->addScript("scripts/externals/typeahead.js");
$loader->addScript("scripts/externals/bootstrap-tagsinput.min.js");
$loader->addScript("scripts/helpers.js");
$loader->addScript("scripts/ajaxlinks.js");
$loader->addScript("scripts/forms.js");
$loader->addScript("scripts/messages.js");
$loader->addScript("scripts/overlay.js");

// run and output
$app->run();


?>
