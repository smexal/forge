<?php 

class FourOhFour extends AbstractView {
    public $name = '404';

    public function content() {
        return $this->app->render(TEMPLATE_DIR."views/", "404", array(
            'title' => i('Four Oh! Four'),
            'text' => i('The requested page could not be loaded.')
        ));
    }
}