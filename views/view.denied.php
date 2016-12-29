<?php

namespace Forge\Views;

use \Forge\Core\Abstracts as Abstracts;

class PermissionDenied extends Abstracts\View {
    public $name = 'denied';

    public function content() {
        return $this->app->render(CORE_TEMPLATE_DIR."views/", "denied", array(
            'title' => i('Access denied'),
            'text' => i('You do not have the required permission to view this page.')
        ));
    }
}

?>