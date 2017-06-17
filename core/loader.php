<?php

namespace Forge;

use \Forge\Core\Classes\Settings;
use \Forge\Core\Classes\Logger;
use \Forge\Core\Classes\Utils;
use \Forge\Core\App\ViewManager;

/*
    This Class is here to provide loader functionalities
    for various ressource e.g. classes or
    provide script or style tags
*/
class Loader {
    private $scripts = array();
    private $styles = array();
    private $lessc = null;
    private $lessVariablesSet = false;

    static private $instance = null;

    static public function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function manageStyles() {
      // required styles
      $this->addStyle('core/ressources/css/externals/bootstrap.core.min.css', false, false);
      $this->addStyle('core/ressources/css/externals/bootstrap-datetimepicker.min.css', false, false);
      $this->addStyle('core/ressources/css/externals/tooltipster.bundle.min.css', false, false);
      $this->addStyle('core/ressources/css/externals/material-icons.css', false, false);

      // google icons & google's roboto font
      $this->addStyle('//fonts.googleapis.com/css?family=Roboto:100,400,400i,500,700', true, false);

      // base
      $this->addStyle('core/ressources/css/base/main.less', false, false);
      $this->addStyle('core/ressources/css/base/nav.less', false, false);

      // blocks
      $this->addStyle('core/ressources/css/blocks/page-header.less', false, false);
      $this->addStyle('core/ressources/css/blocks/buttons.less', false, false);
      $this->addStyle('core/ressources/css/blocks/card.less', false, false);
      $this->addStyle('core/ressources/css/blocks/table.less', false, false);
      $this->addStyle('core/ressources/css/blocks/overlay.less', false, false);
      $this->addStyle('core/ressources/css/blocks/form.less', false, false);
      $this->addStyle('core/ressources/css/blocks/dragsort.less', false, false);
      $this->addStyle('core/ressources/css/blocks/list-content.less', false, false);
      $this->addStyle('core/ressources/css/blocks/tagsinput.less', false, false);
      $this->addStyle('core/ressources/css/blocks/builder.less', false, false);
      $this->addStyle('core/ressources/css/blocks/accordion.less', false, false);
      $this->addStyle('core/ressources/css/blocks/dropzone.less', false, false);
      $this->addStyle('core/ressources/css/blocks/media-library.less', false, false);
      $this->addStyle('core/ressources/css/blocks/grid.less', false, false);
      $this->addStyle('core/ressources/css/blocks/tableeditbar.less', false, false);
      $this->addStyle('core/ressources/css/blocks/spinner.less', false, false);

      /* TODO nightmode css not yet implemented in redesign-42
      if((array_key_exists('night', $_GET) || Settings::get('nightmode')) && ! array_key_exists('day', $_GET)) {
          $this->addStyle('core/ressources/css/nightmode.less', false, 'manage');
      }*/
    }

    public function setLessVariables() {
      if (!$this->lessVariablesSet) {
        $prim = '#B2FF59';
        $set = Settings::get('primary_color');
        if ($set) {
          $prim = $set;
        }
        $this->lessc->setVariables(array(
          "color-primary" => "#B2FF59",
          "color-accent" => "#4194e1",
          "color-gray-dark" => "#212121",
          "color-gray" => "#343434",
          "color-gray-middle" => "#ABABAB",
          "color-gray-lighter" => "#E0E0E0",
          "color-gray-light" => "#f0f0f0",
          "color-danger" => "#d9534f"
        ));
      }
      $this->lessVariablesSet = true;
    }

    // gets called on app initialization
    public function prepare() {
        if (is_null($this->lessc)) {
            $this->lessc = new \lessc;
        }
    }

    private function __construct(){
        $this->libraries();
        $this->loadCoreScripts();
        $this->loadModules();

    }

    private function libraries() {
      // load external ressources
      $this->addLibrary("raintpl/rain.tpl.class.php");
      $this->addLibrary("helpers/additional_functions.php");
      $this->addLibrary("helpers/core_facade.php");
      $this->addLibrary("mysqlidb/mysqlidb.php");
      $this->addLibrary("lessc/lessc.inc.php");
    }

    public function addLibrary($path) {
        require_once(CORE_ROOT."libs/".$path);
    }

    public function addScript($script, $absolute=false) {
        if (!$absolute)
            $script = WWW_ROOT.$script;
        array_push($this->scripts, $script);
    }
    public function getScripts() {
        return $this->scripts;
    }

    public function addStyle($style, $absolute=false, $viewCondition = false) {
        $this->setLessVariables();
        if (!$absolute && ! strstr($style, ".less")) {
          $style = WWW_ROOT.$style;
        }
        if (!$absolute && strstr($style, ".less")) {
          $style = $this->compileLess($style);
        }
        if ($viewCondition) {
          if (in_array($viewCondition, Utils::getUriComponents())) {
            array_push($this->styles, $style);
          }
        } else {
          array_push($this->styles, $style);
        }

    }
    public function getStyles() {
        return $this->styles;
    }

    public function compileLess($less) {
        $less_path = DOC_ROOT.$less;
        if (file_exists($less)) {
            $pathinfo = pathinfo($less_path);
            $base_uri = str_replace($pathinfo['basename'], "", $less);
            $css_file = str_replace(".less", ".css", DOC_ROOT."core/ressources/css/__compiled/".$pathinfo['basename']);
            $run = false;
            if (file_exists($css_file) && filemtime($less_path) > filemtime($css_file))
                $run = true;
            if (!file_exists($css_file))
                $run = true;
            if ($run) {
                if ($handle = fopen($css_file, "w")) {
                    $content = $this->lessc->compileFile($less_path);
                    fwrite($handle, $content);
                    fclose($handle);
                } else {
                  Logger::error("Problems while compiling less: Cannot write css file.");
                }
            }
            return WWW_ROOT."core/ressources/css/__compiled/".$pathinfo['filename'].".css";
        }
    }

    public function loadComponents() {
        $this->loadDirectory(CORE_ROOT."components/");
    }

    public function loadApp() {
        $this->loadDirectory(CORE_ROOT."app/");
    }

    public function loadModules() {
      $this->loadDirectory(DOC_ROOT."modules/", false, "module.php");
    }

    public function loadTraits() {
      $this->loadDirectory(CORE_ROOT."traits/");
    }

    public function loadAbstracts() {
      $this->loadDirectory(CORE_ROOT."abstracts/");
    }

    public function loadClasses() {
        $this->loadDirectory(CORE_ROOT."classes/");
    }

    public function loadInterfaces() {
        $this->loadDirectory(CORE_ROOT."interfaces/");
    }

    public function loadViews() {

    }

    private function loadCoreScripts() {
      $this->addScript("core/ressources/scripts/externals/jquery.js");
      $this->addScript("core/ressources/scripts/externals/jquery-ui.js");
      $this->addScript("core/ressources/scripts/externals/bootstrap.js");
      $this->addScript("core/ressources/scripts/externals/typeahead.js");
      $this->addScript("core/ressources/scripts/externals/bootstrap-tagsinput.min.js");
      $this->addScript("core/ressources/scripts/externals/tooltipster.bundle.min.js");
      $this->addScript("core/ressources/scripts/externals/tinymce/tinymce.min.js");
      $this->addScript("core/ressources/scripts/externals/moment-with-locales.min.js");
      $this->addScript("core/ressources/scripts/externals/bootstrap-datetimepicker.min.js");
      $this->addScript("core/ressources/scripts/externals/dropzone.js");
      $this->addScript("core/ressources/scripts/api.js");
      $this->addScript("core/ressources/scripts/dropzone.js");
      $this->addScript("core/ressources/scripts/tinymce.js");
      $this->addScript("core/ressources/scripts/helpers.js");
      $this->addScript("core/ressources/scripts/ajaxlinks.js");
      $this->addScript("core/ressources/scripts/forms.js");
      $this->addScript("core/ressources/scripts/messages.js");
      $this->addScript("core/ressources/scripts/overlay.js");
      $this->addScript("core/ressources/scripts/dragsort.js");
      $this->addScript("core/ressources/scripts/accordion.js");
    }

    public function loadDirectory($directory, $inquery=false, $filefilter=false, $namepattern = false) {
      if (file_exists($directory)) {
        $dir = new \DirectoryIterator($directory);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot()) {
              continue;
            }

            if (strstr($fileinfo->getFilename(), ".php")) {
                if (! $filefilter || $filefilter == $fileinfo->getFilename()) {
                  if (!$namepattern) {
                    $f = $directory.$fileinfo->getFilename();
                    require_once($f);
                  } else {
                    foreach ($namepattern as $pattern) {
                      $fileparts = explode(".", $fileinfo->getFilename());
                      if (in_array($pattern, $fileparts)) {
                        require_once($directory.$fileinfo->getFilename());
                        break;
                      }
                    }
                  }
                }
            } elseif ($fileinfo->isDir()) {
              // check if the subdirectory is part of the queried url. (no manage views without manage queried)
              if ($inquery) {
                if (in_array($fileinfo->getFilename(), Utils::getUriComponents())) {
                  $this->loadDirectory($directory.$fileinfo->getFilename()."/", false, $filefilter, $namepattern);
                }
              } else {
                $this->loadDirectory($directory.$fileinfo->getFilename()."/", false, $filefilter, $namepattern);
              }
            }
        }
      }
    }
    private function __clone(){}
}
