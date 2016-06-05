<?php
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

    public function setLessVariables() {
      if(!$this->lessVariablesSet) {
        $this->lessc->setVariables(array(
          "primary_color" => "#66BB6A",
          "accent_color" => "#4194e1",
          "dark_grey" => "#262626",
          "light_grey" => "#f0f0f0"
        ));
      }
      $this->lessVariablesSet = true;
    }

    // gets called on app initialization
    public function prepare() {
        if(is_null($this->lessc)) {
            $this->lessc = new lessc;
        }
    }

    private function __construct(){
        $this->ressources();
        $this->loadCoreScripts();
        $this->loadInterfaces();
        $this->loadAbstracts();
        $this->loadClasses();
        $this->loadModules();
        $this->loadViews();
        $this->loadComponents();
        $this->loadApp();
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
        if(!$absolute)
            $script = WWW_ROOT.$script;
        array_push($this->scripts, $script);
    }
    public function getScripts() {
        return $this->scripts;
    }

    public function addStyle($style, $absolute=false, $viewCondition = false) {
        $this->setLessVariables();
        if(!$absolute && ! strstr($style, ".less"))
            $style = WWW_ROOT.$style;
        if(!$absolute && strstr($style, ".less"))
            $style = $this->compileLess($style);
        if($viewCondition) {
          if(in_array($viewCondition, Utils::getUriComponents())) {
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
        if(file_exists($less)) {
            $pathinfo = pathinfo($less_path);
            $base_uri = str_replace($pathinfo['basename'], "", $less);
            $css_file = str_replace(".less", ".css", DOC_ROOT."core/css/compiled/".$pathinfo['basename']);
            $run = false;
            if(file_exists($css_file) && filemtime($less_path) > filemtime($css_file))
                $run = true;
            if(!file_exists($css_file))
                $run = true;
            if($run) {
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
      $this->loadDirectory(CORE_ROOT."views/", true);
    }

    private function loadCoreScripts() {
      $this->addScript("core/scripts/externals/jquery.js");
      $this->addScript("core/scripts/externals/bootstrap.js");
      $this->addScript("core/scripts/externals/typeahead.js");
      $this->addScript("core/scripts/externals/bootstrap-tagsinput.min.js");
      $this->addScript("core/scripts/externals/tinymce/tinymce.min.js");
      $this->addScript("core/scripts/externals/dropzone.js");
      $this->addScript("core/scripts/dropzone.js");
      $this->addScript("core/scripts/tinymce.js");
      $this->addScript("core/scripts/helpers.js");
      $this->addScript("core/scripts/ajaxlinks.js");
      $this->addScript("core/scripts/forms.js");
      $this->addScript("core/scripts/messages.js");
      $this->addScript("core/scripts/overlay.js");
    }

    public function loadDirectory($directory, $inquery=false, $filefilter=false) {
        $dir = new DirectoryIterator($directory);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() &&  strstr($fileinfo->getFilename(), ".php")) {
                if(! $filefilter || $filefilter == $fileinfo->getFilename()) {
                    require_once($directory.$fileinfo->getFilename());
                }
            } elseif(!$fileinfo->isDot() && $fileinfo->isDir()) {
              // check if the subdirectory is part of the queried url. (no manage views without manage queried)
              if($inquery) {
                if(in_array($fileinfo->getFilename(), Utils::getUriComponents())) {
                  $this->loadDirectory($directory.$fileinfo->getFilename()."/", false, $filefilter);
                }
              } else {
                $this->loadDirectory($directory.$fileinfo->getFilename()."/", false, $filefilter);
              }
            }
        }
    }
    private function __clone(){}
}

?>
