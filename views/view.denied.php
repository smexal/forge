<?php

namespace Forge\Views;

use \Forge\Core\Abstracts\View;

use function \Forge\Core\Classes\i;

class PermissionDenied extends View {
    public $name = 'denied';

    public function content() {
        header("HTTP/1.0 403 Forbidden");

        return $this->app->render(CORE_TEMPLATE_DIR."views/", "denied", array(
            'title' => i('Access denied'),
            'text' => i('You do not have the required permission to view this page.')
        ));
    }
}

?>
