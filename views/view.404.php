<?php 

class FourOhFour extends AbstractView {
    public $name = '404';

    public function content() {
        header("HTTP/1.0 404 Not Found");
        
        return $this->app->render(CORE_TEMPLATE_DIR."views/", "404", array(
            'title' => i('Four Oh! Four'),
            'text' => i('The requested page could not be loaded.')
        ));
    }
}