<?php

abstract class Component implements IComponent {
    public $id = null;
    public $prefs = array();

    public function __construct() {
        $this->prefs = $this->prefs();
    }

    public function getPref($key) {
        if(array_key_exists($key, $this->prefs)) {
            return $this->prefs[$key];
        } else {
            Logger::debug("Unknown Pref '".$key."' for Component '".get_called_class()."'");
            return false;
        }
    }

    public function getPrefSet() {
        return $this->prefs;
    }

    public function prefs() {
        return false;
    }
}

?>
