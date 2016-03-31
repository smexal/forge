<?php

class PageBuilderManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'pages';
    public $permission = 'manage.builder.pages';
    public $permissions = array(
    );

    public function content($uri=array()) {
      if(count($uri) == 0) {
        return $this->defaultContent();
      }
    }

    private function defaultContent() {
      return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
          'title' => i('Pages'),
          'content' => $this->pageList(),
          'global_actions' => ''
      ));
    }

    private function pageList() {
      $pages = new Pages();
      $items = '';
      foreach($pages->get() as $p) {
        $page = new Page($p['id']);
        $items.=$this->app->render(CORE_TEMPLATE_DIR."assets/", "list-item", array(
          'value' => $page->name
        ));
      }
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "list", array(
        'items' => $items
      ));
    }
}

?>
