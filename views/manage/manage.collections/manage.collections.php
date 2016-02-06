<?php

class CollectionManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'collections';
    public $permission = 'manage.collections';

    private $collection = false;

    public function content($uri=array()) {
      // find out which collection we are editing
      foreach( $this->app->cm->collections as $collection) {
        if($collection->getPref('name') == $uri[0]) {
          $this->collection = $collection;
          break;
        }
      }

      // check if user has permission
      if(Auth::allowed($collection->permission)) {
        return $this->app->render(TEMPLATE_DIR."views/sites/", "generic", array(
            'title' => $this->collection->getPref('all-title'),
            'content' => $this->manageForm()
        ));
      } else {
        $this->app->redirect("denied");
      }
    }

    private function manageForm() {
      return 'yh';
    }
}

?>
