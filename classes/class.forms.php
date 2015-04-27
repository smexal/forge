<?php

class Form {
    private $content = array();
    private $app = null;
    private $horizontal = false;
    private $noAutocomplete = false;

    public function __construct() {
        if(is_null($this->app)) {
            $this->app = App::instance();
        }
    }

    public function disableAuto() {
        $this->noAutocomplete = true;
    }

    public function horizontal() {
        $this->horizontal = true;
    }

    public function hidden($name, $value) {
        array_push($this->content, $this->app->render(TEMPLATE_DIR."assets/", "hidden", array(
            'name' => $name,
            'value' => $value
        )));
    }

    public function input($name, $id, $label, $type="input") {
        array_push($this->content, $this->app->render(TEMPLATE_DIR."assets/", "input", array(
            'name' => $name,
            'id' => $id,
            'label' => $label,
            'type' => $type,
            'hor' => $this->horizontal,
            'noautocomplete' => $this->noAutocomplete
        )));
    }

    public function submit($text, $level="primary") {
        array_push($this->content, $this->app->render(TEMPLATE_DIR."assets/", "submit", array(
            'text' => $text,
            'level' => $level,
            'hor' => $this->horizontal
        )));
    }

    public function render($method="POST") {
        return $this->app->render(TEMPLATE_DIR."assets/", "form", array(
            'method' => $method,
            'content' => $this->content,
            'horizontal' => $this->horizontal
        ));
    }
    
}

?>