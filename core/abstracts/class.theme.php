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
    public $ajaxLayout = '';
    public $lessVariables = array();
    private $runMinifyCSS = false;
    private $runMinifyJS = false;

    public function tinyUrl() {
        return '';
    }

    public function tinyFormats() {
        return '';
    }

    public function addScript($script, $absolute=false, $index = false, $defer = false) {
        foreach($this->load_scripts as $s) {
            if(array_key_exists('source', $s) && $s['source'] == $script) {
                return;
            }
        }

        if(MINIFY && ! $this->isExternalRessource($script)) {
            if($absolute) {
                $oneFile = $_SERVER['DOCUMENT_ROOT'].$script;
            } else {
                $oneFile = $this->directory().$script;
            }
            $allMin = $this->directory().'assets/all.min.js';
            if(! file_exists($allMin)) {
                $this->runMinifyJS = true;
            }
            if(file_exists($allMin) && filemtime($oneFile) > filemtime($allMin)) {
                $this->runMinifyJS = true;
            }
        }

        if(!$absolute) {
            $script = $this->url().$script;
        }
        if($defer) {
            $this->defered_scripts[] = $script;
            return;
        }

        $scriptToAdd = [
            'source' => $script,
            'external' => $this->isExternalRessource($script)
        ];

        if ($index || $index === 0) {
            array_unshift($this->load_scripts, $scriptToAdd);
        } else {
            array_push($this->load_scripts, $scriptToAdd);
        }
    }

    private function isExternalRessource($script) {
        if(strstr($script, "http://")) {
            return true;
        }
        if(strstr($script, "https://")) {
            return true;
        }
        if(substr($script, 0, 2) == '//') {
            return true;
        }
        return false;
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
        if(is_null($this->lessc)) {
            $this->lessc = new \lessc;
        }
        $this->lessc->setVariables($this->lessVariables);

        if(in_array($style, $this->styles)) {
            return;
        }
        if(!$absolute && strstr($style, ".less")) {
            $style = $this->compileLess($style);
        }
        if($viewCondition) {
            if(in_array($viewCondition, Utils::getUriComponents())) {
                $this->styles[] =[
                    'source' => $style,
                    'absolute' => $absolute
                ];
            }
        } else {
            $this->styles[] =[
                'source' => $style,
                'absolute' => $absolute
            ];
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
                $this->runMinifyCSS = true;
                Settings::set('css_version_number', uniqid());
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

    private function loadMinifier() {
        $path = CORE_ROOT.'libs/';
        require_once $path . 'minify/src/Minify.php';
        require_once $path . 'minify/src/CSS.php';
        require_once $path . 'minify/src/JS.php';
        require_once $path . 'minify/src/Exception.php';
        require_once $path . 'minify/src/Exceptions/BasicException.php';
        require_once $path . 'minify/src/Exceptions/FileImportException.php';
        require_once $path . 'minify/src/Exceptions/IOException.php';
        require_once $path . 'path-converter/src/ConverterInterface.php';
        require_once $path . 'path-converter/src/Converter.php';
    }

    public function header() {
        $eventContent = App::instance()->eh->fire("onLoadHeader");
        if(is_null($eventContent)) {
            $eventContent = false;
        }

        if(MINIFY) {
            $this->loadMinifier();
        }

        $this->scripts();

        // add required core scripts
        $this->addScript(CORE_WWW_ROOT."ressources/scripts/externals/jquery.js", true, 0);
        $this->addScript(CORE_WWW_ROOT."ressources/scripts/externals/bootstrap.js", true);
        $this->addScript(CORE_WWW_ROOT."ressources/scripts/helpers.js", true);
        $this->addScript(CORE_WWW_ROOT."ressources/scripts/externals/tooltipster.bundle.min.js", true);

        $return = App::instance()->render(CORE_TEMPLATE_DIR, "head", array(
            'title' => $this->getTitle(),
            'scripts' => $this->prepareScripts(),
            'build_no' => Settings::get('css_version_number'),
            'styles' => $this->prepareStyles(),
            'favicon' => false,
            'eventContent' => $eventContent,
            'custom' => $this->customHeader(),
            'defered_scripts' => $this->defered_scripts
        ));
        return $return;
    }

    private function prepareScripts() {
        $scripts = [];

        if(! MINIFY) {
            foreach($this->load_scripts as $script) {
                $scripts[] = $script['source'];
            }
            return $scripts;
        }

        $minifier = null;
        foreach($this->load_scripts as $script) {
            // ignore absolute loaded files..
            if($script['external']) {
                $scripts[] = $script['source'];
                continue;
            }
            if($this->runMinifyJS) {
                if(is_null($minifier)) {
                    $minifier = new \MatthiasMullie\Minify\JS($_SERVER['DOCUMENT_ROOT'].$script['source']);
                } else {
                    $minifier->add($_SERVER['DOCUMENT_ROOT'].$script['source']);
                }
            }
        }
        if($this->runMinifyJS) {
            $minifiedPath = $this->directory()."assets/all.min.js";
            $minifier->minify($minifiedPath);
        }
        $scripts[] = $this->url()."assets/all.min.js";

        return $scripts;
    }

    private function prepareStyles() {
        $styles = [];

        if(! MINIFY) {
            foreach($this->styles as $style) {
                $styles[] = $style['source'];
            }
            return $styles;
        }

        $minifier = null;
        foreach($this->styles as $style) {
            // ignore absolute loaded files..
            if($style['absolute']) {
                $styles[] = $style['source'];
                continue;
            }

            if($this->runMinifyCSS) {
                if(is_null($minifier)) {
                    $minifier = new \MatthiasMullie\Minify\CSS($_SERVER['DOCUMENT_ROOT'].$style['source']);
                } else {
                    $minifier->add($_SERVER['DOCUMENT_ROOT'].$style['source']);
                }
            }
        }
        if($this->runMinifyCSS) {
            $minifiedPath = $this->directory()."css/compiled/all.min.css";
            $minifier->minify($minifiedPath);
        }
        $styles[] = $this->url()."css/compiled/all.min.css";

        return $styles;
    }

}

