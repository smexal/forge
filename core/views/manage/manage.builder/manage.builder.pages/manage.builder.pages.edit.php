<?php

class ManagePageEdit extends AbstractView {
    public $parent = 'pages';
    public $name = 'edit';
    public $permission = 'manage.builder.pages.edit';
    public $permissions = array(
    );

    private $page = null;

    public function content($uri=array()) {
      if(is_numeric($uri[0])) {
        $this->page = new Page($uri[0]);
        return $this->defaultContent();
      }
    }

    private function defaultContent() {
      return $this->app->render(CORE_TEMPLATE_DIR."views/", "builder", array(
          'title' => sprintf(i('Edit `%s`'), $this->page->name),
          'backurl' => Utils::getUrl(array('manage', 'pages')),
          'backname' => i('back to overview'),
          'panel_left' => 'left',
          'panel_right' => 'right'
      ));
    }
}

?>
