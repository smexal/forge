<?php

namespace Forge;

use \Forge\Core\Classes\Settings;
use \Forge\Core\Classes\Logger;
use \Forge\Core\Classes\Utils;

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
      $this->addStyle("core/css/externals/bootstrap.core.min.css", false, false);
      $this->addStyle("core/css/externals/bootstrap-datetimepicker.min.css", false, false);
      $this->addStyle("core/css/externals/tooltipster.bundle.min.css", false, false);

      // admin styles
      $this->addStyle("core/css/bootstrap.less", false, "manage");
      $this->addStyle("core/css/tagsinput.less", false, "manage");
      $this->addStyle("core/css/overlay.less", false, "manage");
      $this->addStyle("core/css/layout.less", false, "manage");
      $this->addStyle("core/css/elements.less", false, "manage");

      $this->addStyle("core/css/modules/builder.less", false, "manage");
      $this->addStyle("core/css/modules/form.less", false, "manage");
      $this->addStyle("core/css/modules/dropzone.less", false, "manage");
      $this->addStyle("core/css/modules/media.less", false, "manage");

      $this->addStyle("core/css/loader.less", false, "manage");
      $this->addStyle("core/css/fonts.less", false, "manage");
    }

    public function setLessVariables() {
      if (!$this->lessVariablesSet) {
        $prim = '#4194e1';
        $set = Settings::get('primary_color');
        if ($set) {
          $prim = $set;
        }
        $this->lessc->setVariables(array(
          "primary_color" => $prim,
          "accent_color" => "#4194e1",
          "dark_grey" => "#262626",
          "light_grey" => "#f0f0f0"
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
        $this->ressources();
        $this->loadCoreScripts();
        $this->loadInterfaces();
        $this->loadTraits();
        $this->loadAbstracts();
        $this->loadClasses();
        $this->loadModules();
        $this->loadApp();$this->loadViews();
        $this->loadComponents();

    }

    private function ressources() {
      // load external ressources
      $this->addRessource("raintpl/rain.tpl.class.php");
      $this->addRessource("helpers/additional_functions.php");
      $this->addRessource("mysqlidb/mysqlidb.php");
      $this->addRessource("lessc/lessc.inc.php");
    }

    public function addRessource($path) {
        require_once(CORE_ROOT."ressources/".$path);
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
            $css_file = str_replace(".less", ".css", DOC_ROOT."core/css/compiled/".$pathinfo['basename']);
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
            return WWW_ROOT."core/css/compiled/".$pathinfo['filename'].".css";
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
      // load general views
      $this->loadDirectory(DOC_ROOT."views/", true);

      // load core views
      $this->loadDirectory(CORE_ROOT."views/", true, false, Utils::getUriComponents());
    }

    private function loadCoreScripts() {
      $this->addScript("core/scripts/externals/jquery.js");
      $this->addScript("core/scripts/externals/bootstrap.js");
      $this->addScript("core/scripts/externals/typeahead.js");
      $this->addScript("core/scripts/externals/bootstrap-tagsinput.min.js");
      $this->addScript("core/scripts/externals/tooltipster.bundle.min.js");
      $this->addScript("core/scripts/externals/tinymce/tinymce.min.js");
      $this->addScript("core/scripts/externals/moment-with-locales.min.js");
      $this->addScript("core/scripts/externals/bootstrap-datetimepicker.min.js");
      $this->addScript("core/scripts/externals/dropzone.js");
      $this->addScript("core/scripts/dropzone.js");
      $this->addScript("core/scripts/tinymce.js");
      $this->addScript("core/scripts/helpers.js");
      $this->addScript("core/scripts/ajaxlinks.js");
      $this->addScript("core/scripts/forms.js");
      $this->addScript("core/scripts/messages.js");
      $this->addScript("core/scripts/overlay.js");
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

?>
