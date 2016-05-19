<?php

class ThemeManager {
    public function __construct() {
    }

    public function getThemes() {
        $theme_directory = DOC_ROOT."themes/";
        $dir = scandir($theme_directory);
        $valid_themes = array();
        var_dump($theme_directory);
        var_dump($dir);
        foreach($dir as $theme) {
            if($this->isValid($theme_directory, $theme)) {
                array_push($valid_themes, $theme);
            }
        }
    }

    private function isValid($path, $name) {
        if(file_exists($path.$name."/theme.php")) {
            return true;
        }
    }
}

?>
