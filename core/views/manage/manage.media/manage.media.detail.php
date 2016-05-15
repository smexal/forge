<?php

class MediaManagentDetail extends AbstractView {
    public $parent = 'media';
    public $name = 'detail';
    public $permission = 'manage.media';
    public $permissions = array(
    );

    public function content($uri=array()) {
        return 'yes';
    }
}

?>
