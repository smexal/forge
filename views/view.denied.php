<?php 

class PermissionDenied extends AbstractView {
    public $name = 'denied';

    public function content() {
        return '<h1>permission denied<h1>';
    }
}

?>