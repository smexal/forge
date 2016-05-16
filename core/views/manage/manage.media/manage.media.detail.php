<?php

class MediaManagentDetail extends AbstractView {
    public $parent = 'media';
    public $name = 'detail';
    public $permission = 'manage.media';
    public $permissions = array(
    );
    private $media = null;

    public function content($uri=array()) {
        if(is_numeric($uri[0])) {
            $this->media = new Media($uri[0]);
            return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
                'title' => sprintf(i('Details to `%1$s`'), $this->media->title),
                'message' => '',
                'form' => $this->modifyForm()
            ));
        }
    }

    private function modifyForm() {
        return 'form';
    }
}

?>
