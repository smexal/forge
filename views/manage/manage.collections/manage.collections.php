<?php

class CollectionManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'collections';
    public $permission = 'manage.collections';
    public $permissions = array(
      'add' => "manage.collections.add"
    );

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

        // render subview
        if(count($uri) > 1 && $uri[1] == 'add') {
            return $this->getSubview('add', $this);

        // render the overview
        } else {
          if(Auth::allowed($this->permissions['add'])) {
            $add_button = $this->app->render(TEMPLATE_DIR."assets/", "overlay-button", array(
              'url' => Utils::getUrl(array('manage', 'collections', $this->collection->getPref('name'), 'add')),
              'label' => $this->collection->getPref('add-label')
            ));
          } else {
            $add_button = '';
          }
          return $this->app->render(TEMPLATE_DIR."views/sites/", "generic", array(
              'title' => $this->collection->getPref('all-title'),
              'global_actions' => $add_button,
              'content' => $this->manageForm()
          ));
        }

      } else {
        $this->app->redirect("denied");
      }
    }

    private function manageForm() {
      return 'yh';
    }
}

?>
