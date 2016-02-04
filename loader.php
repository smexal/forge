<?php
/*
    This Class is here to provide loader functionalities
    for various ressource e.g. autoload php files or
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
          "primary_color" => "#1bd27e",
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
        $this->loadInterfaces();
        $this->loadClasses();
        $this->loadViews();
        $this->loadApp();
    }

    public function addRessource($path) {
        require_once(DOC_ROOT."ressources/".$path);
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
            $css_file = str_replace(".less", ".css", $pathinfo['dirname']."/compiled/".$pathinfo['basename']);
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
            return WWW_ROOT."css/compiled/".$pathinfo['filename'].".css";
        }
    }

    public function loadApp() {
        $this->loadDirectory(DOC_ROOT."app/");
    }

    public function loadClasses() {
        $this->loadDirectory(DOC_ROOT."classes/");
    }

    public function loadInterfaces() {
        $this->loadDirectory(DOC_ROOT."interfaces/");
    }

    public function loadViews() {
        $this->loadDirectory(DOC_ROOT."views/", true);
    }

    public function loadDirectory($directory, $inquery=false) {
        $dir = new DirectoryIterator($directory);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() &&  strstr($fileinfo->getFilename(), ".php")) {
                require_once($directory.$fileinfo->getFilename());
            } elseif(!$fileinfo->isDot() && $fileinfo->isDir()) {
              // check if the subdirectory is part of the queried url. (no manage views without manage queried)
              if($inquery) {
                if(in_array($fileinfo->getFilename(), Utils::getUriComponents())) {
                  $this->loadDirectory($directory.$fileinfo->getFilename()."/");
                }
              } else {
                $this->loadDirectory($directory.$fileinfo->getFilename()."/");
              }
            }
        }
    }
    private function __clone(){}
}

?>
