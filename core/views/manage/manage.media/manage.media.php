<?php

class MediaManagent extends AbstractView {
    public $parent = 'manage';
    public $name = 'media';
    public $permission = 'manage.media';
    public $permissions = array(
    );

    public function content($uri=array()) {
      if(count($uri) > 0) {
      } else {
        return $this->ownContent();
      }
    }

    private function ownContent() {
      return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
          'title' => i('Module Management'),
          'global_actions' => '',
          'content' => 'content'
      ));
    }
}

?>
