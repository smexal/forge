<?php

class Form {
    private $content = array();
    private $app = null;
    private $horizontal = false;
    private $noAutocomplete = false;
    private $action = false;
    private $ajax = false;
    private $ajaxTarget = false;

    public function __construct($action=false) {
        if(is_null($this->app)) {
            $this->app = App::instance();
        }
        $this->action = $action;
    }

    public function disableAuto() {
        $this->noAutocomplete = true;
    }

    public function horizontal() {
        $this->horizontal = true;
    }

    public function ajax($target=".content") {
        $this->ajax = true;
        $this->ajaxTarget = $target;
    }

    public function hidden($name, $value) {
        array_push($this->content, $this->app->render(TEMPLATE_DIR."assets/", "hidden", array(
            'name' => $name,
            'value' => $value
        )));
    }

    public function input($name, $id, $label, $type="input", $value=false, $hint=false) {
      array_push($this->content, $this->app->render(TEMPLATE_DIR."assets/", "input", array(
        'name' => $name,
        'id' => $id,
        'label' => $label,
        'type' => $type,
        'hor' => $this->horizontal,
        'noautocomplete' => $this->noAutocomplete,
        'value' => $value,
        'hint' => $hint
      )));
    }
    
    public function area($id, $label, $value=false, $hint = false) {
      array_push($this->content, $this->app->render(TEMPLATE_DIR."assets/", "textarea", array(
          'id' => $id,
          'name' => $id,
          'label' => $label,
          'value' => $value,
          'hint' => $hint
      )));
    }

    public function tags($name, $id, $label, $values=false, $getter=false) {
      if($values && !is_null($values)) {
        $values = Utils::json($values);
      }
      if($getter) {
        if(!is_array($getter) || ! array_key_exists("value", $getter) || ! array_key_exists("name", $getter) || ! array_key_exists("url", $getter)) {
           throw new Exception("Invalid getter given. value, name and url required in assoc array.");
        }
      }
      array_push($this->content, $this->app->render(TEMPLATE_DIR."assets/", "tagsinput", array(
        'name' => $name,
        'id' => $id,
        'label' => $label,
        'getter' => $getter,
        'values' => $values,
        'hor' => $this->horizontal
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
            'action' => $this->action,
            'ajax' => $this->ajax,
            'ajax_target' => $this->ajaxTarget,
            'method' => $method,
            'content' => $this->content,
            'horizontal' => $this->horizontal
        ));
    }

}

?>
