<?php

class User {
    private $app;
    private $data = false;

    public function __construct($id) {
        $this->data['id'] = $id;
    }

    public function get($field) {
        if(array_key_exists($field, $this->data))
            return $this->data[$field];
    }

}

?>