<?php 

class PermissionDenied extends AbstractView {
    public $name = 'denied';

    public function content() {
        return 'permission denied';
    }
}

?>