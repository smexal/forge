<?php

namespace Forge\Views;

use \Forge\Core\Abstracts\View;


class DeniedView extends View {
    public $name = 'denied';

    public function content($uri=[]) {
        header("HTTP/1.0 403 Forbidden");

        return $this->app->render(CORE_TEMPLATE_DIR."views/", "denied", array(
            'title' => i('Access denied'),
            'text' => i('You do not have the required permission to view this page.')
        ));
    }
}

?>
