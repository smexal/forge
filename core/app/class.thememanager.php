<?php

namespace Forge\Core\App;

class ThemeManager {
    public $active = '';
    public $theme = '';
    public $theme_directory = '';

    public function __construct() {
        // set current active theme.
        $this->theme_directory = DOC_ROOT."themes/";
        $this->active = Settings::get('active_theme');
        $this->loadTheme();
        $this->instance();
    }

    public function getTemplateDirectory($name = 'layout') {
        return $this->theme_directory.$this->active."/templates/";
    }

    private function instance() {
        $classes = get_declared_classes();
        $implementsIModule = array();
        foreach($classes as $klass) {
            $reflect = new ReflectionClass($klass);
            if($reflect->implementsInterface('ITheme')) {
                $rc = new ReflectionClass($klass);
                if(! $rc->isAbstract()) {
                    $this->theme = $klass::instance();
                    return true;
                }
            }
        }
        return false;
    }

    private function loadTheme() {
        if(is_null($this->active)) {
            // there is no theme, just take the first one you can find...
            $themes = $this->getThemes();
            $this->active = reset($themes);
        }
        if($this->active && $this->active != '') {
            $theme_root = $this->theme_directory.$this->active."/theme.php";
            if(file_exists($theme_root)) {
                require_once($theme_root);
            } else {
                Logger::error('Could not load theme `'.$this->active.'`, theme.php not found in `'.$theme_root.'`');
            }
        }
    }

    public function getThemes() {
        $dir = scandir($this->theme_directory);
        $valid_themes = array();
        foreach($dir as $theme) {
            if($this->isValid($this->theme_directory, $theme)) {
                $valid_themes[$theme] = $theme;
            }
        }
        return $valid_themes;
    }

    private function isValid($path, $name) {
        if($name == '.' || $name == '..') {
            return false;
        }
        if(file_exists($path.$name."/theme.php")) {
            return true;
        }
        return false;
    }
}

?>
