<?php

abstract class Theme implements ITheme {
    protected static $instances = array();
    private $styles = array();
    private $lessc = null;

    static public function instance() {
        $class = get_called_class();
        if(!array_key_exists($class, static::$instances)) {
            static::$instances[$class] = new $class();
        }
        static::$instances[$class]->id = $class;
        static::$instances[$class]->init();
        return static::$instances[$class];
    }
    private function __construct() {}
    private function __clone() {}

    public function init() {
        if(is_null($this->lessc)) {
            $this->lessc = new lessc;
        }
    }

    public function directory() {
        $tm = App::instance()->tm;
        return $tm->theme_directory.$tm->active.'/';
    }

    public function url() {
        $tm = App::instance()->tm;
        return WWW_ROOT."themes/".$tm->active.'/';
    }

    public function addStyle($style, $absolute=false, $viewCondition=false) {
        if(!$absolute && strstr($style, ".less")) {
            $style = $this->compileLess($style);
        }
        if($viewCondition) {
            if(in_array($viewCondition, Utils::getUriComponents())) {
                array_push($this->styles, $style);
            }
        } else {
            array_push($this->styles, $style);
        }
    }

    public function compileLess($less) {
        if(file_exists($less)) {
            $pathinfo = pathinfo($less);
            $base_uri = str_replace($pathinfo['basename'], "", $less);
            $css_file = str_replace(".less", ".css", $this->directory()."css/compiled/".$pathinfo['basename']);
            if(!file_exists($this->directory()."css/compiled/")) {
                if(!mkdir($this->directory()."css/compiled/", 0655, true)) {
                    Logger::error('Unable to create directory `'.$this->directory()."css/compiled/".'`');
                }
            }
            $run = false;
            if(file_exists($css_file) && filemtime($less) > filemtime($css_file))
                $run = true;
            if(!file_exists($css_file))
                $run = true;
            if($run) {
                if ($handle = fopen($css_file, "w")) {
                    $content = $this->lessc->compileFile($less);
                    fwrite($handle, $content);
                    fclose($handle);
                } else {
                  Logger::error("Problems while compiling less: Cannot write css file.");
                }
            }
            return $this->url()."css/compiled/".$pathinfo['filename'].".css";
        } else {
            Logger::error('Unable to Find File: `'.$less.'`');
        }
        return false;
    }

    public function header() {
        $head = '<meta charset="UTF-8">';
        return App::instance()->render(CORE_TEMPLATE_DIR, "head", array(
            'title' => 'title',
            'scripts' => array(),
            'styles' => $this->styles,
            'favicon' => ''
        ));
        return $head;
    }

}

?>
