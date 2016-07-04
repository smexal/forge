<?php

class ForgeTheme extends Theme {
    public $lessVariables = array(
        "color" => "#3abf00",
    );

    public function tinyUrl() {
        // return URL to compile css file with styles for tinyMCE
        // return $this->url().'css/compiled/main.css';
        return '';
    }

    public function tinyFormats() {
        // define tinyMCE styles.
        return array(
            array(
                'title' => i('Heading 1', 'butterlan-theme'),
                'block' => 'h1'
            )
        );
    }

    public function start() {
    }

    public function globals() {
        return array(
        );
    }

    public function styles() {
        // load core if wanted...
        $this->addStyle(CORE_WWW_ROOT."css/externals/bootstrap.core.min.css", true);
        $this->addStyle(CORE_ROOT."css/theme.less");
    }

    public function scripts() {
        $this->addScript(CORE_WWW_ROOT."scripts/externals/jquery.js", true);
        $this->addScript(CORE_WWW_ROOT."scripts/externals/bootstrap.js", true);
    }

    public function customHeader() {
    }
}

?>
