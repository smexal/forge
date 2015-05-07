<?php 

class PermissionDenied extends AbstractView {
    public $name = 'denied';

    public function content() {
        return $this->app->render(TEMPLATE_DIR."views/", "denied", array(
            'title' => i('Access denied'),
            'text' => i('You do not have the required permission to view this page.')
        ));
    }
}

?>