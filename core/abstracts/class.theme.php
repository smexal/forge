<?php

namespace Forge\Core\Abstracts;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Classes\Logger;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Settings;
use \Forge\Core\Interfaces\ITheme;

abstract class Theme implements ITheme {
    protected static $instances = array();
    private $styles = array();
    private $load_scripts = array();
    private $defered_scripts = [];
    private $lessc = null;
    public $lessVariables = array();

    public function tinyUrl() {
        return '';
    }

    public function tinyFormats() {
        return '';
    }

    public function addScript($script, $absolute=false, $index = false, $defer = false) {
        if(in_array($script, $this->load_scripts)) {
            return;
        }

        if(!$absolute) {
            $script = $this->url().$script;
        }
        if($defer) {
            $this->defered_scripts[] = $script;
        }

        if ($index || $index === 0) {
            if (array_key_exists($index, $this->load_scripts)) {
                array_splice($this->load_scripts, $index, 0, $script);
            }
        } else {
            array_push($this->load_scripts, $script);
        }
    }

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
            $this->lessc = new \lessc;
            $this->lessc->setVariables($this->lessVariables);
        }
    }

    public function scripts() {
        return;
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
        if(in_array($style, $this->styles)) {
            return;
        }
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

    public function getTitle() {
        $global = Settings::get('title_'.Localization::getCurrentLanguage());
        $page = false;
        if(App::instance()->page) {
            $page = App::instance()->page->getMeta('title');
        }
        if(!$page) {
            return $global;
        }
        return $page.' - '.$global;
    }

    public function customHeader() {
        return false;
    }

    public function header() {
        $eventContent = App::instance()->eh->fire("onLoadHeader");
        if(is_null($eventContent)) {
            $eventContent = false;
        }

        $this->scripts();

        // add required core scripts
        $this->addScript(CORE_WWW_ROOT."ressources/scripts/externals/jquery.js", true, 0);
        $this->addScript(CORE_WWW_ROOT."ressources/scripts/externals/bootstrap.js", true, 1);
        $this->addScript(CORE_WWW_ROOT."ressources/scripts/helpers.js", true, 2);
        $this->addScript(CORE_WWW_ROOT."ressources/scripts/externals/tooltipster.bundle.min.js", true, 3);

        $return = App::instance()->render(CORE_TEMPLATE_DIR, "head", array(
            'title' => $this->getTitle(),
            'scripts' => $this->load_scripts,
            'styles' => $this->styles,
            'favicon' => false,
            'eventContent' => $eventContent,
            'custom' => $this->customHeader(),
            'defered_scripts' => $this->defered_scripts
        ));
        return $return;
    }

}

