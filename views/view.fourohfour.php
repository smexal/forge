<?php

namespace Forge\Views;

use \Forge\Core\Abstracts\View;

class FourohfourView extends View {
    public $name = '404';

    public function content($uri=[]) {
        header("HTTP/1.0 404 Not Found");

        return $this->app->render(CORE_TEMPLATE_DIR."views/", "404", array(
            'title' => i('Four Oh! Four'),
            'text' => i('The requested page could not be loaded.')
        ));
    }
}
