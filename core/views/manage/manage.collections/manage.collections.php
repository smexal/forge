<?php

class CollectionManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'collections';
    public $permission = 'manage.collections';
    public $permissions = array(
      'add' => "manage.collections.add",
      'delete' => "manage.collections.delete"
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
        if(count($uri) > 1) {
          return $this->subviews($uri);
        // render the overview
        } else {
          if(Auth::allowed($this->permissions['add'])) {
            $add_button = $this->app->render(CORE_TEMPLATE_DIR."assets/", "overlay-button", array(
              'url' => Utils::getUrl(array('manage', 'collections', $this->collection->getPref('name'), 'add')),
              'label' => $this->collection->getPref('add-label')
            ));
          } else {
            $add_button = '';
          }
          return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
              'title' => $this->collection->getPref('all-title'),
              'global_actions' => $add_button,
              'content' => $this->collectionList()
          ));
        }

      } else {
        $this->app->redirect("denied");
      }
    }

    private function subviews($uri) {
      switch($uri[1]) {
        case 'add':
          return $this->getSubview('add', $this);
        case 'delete':
          return $this->getSubview('delete', $this);
        case 'edit': 
          return $this->getSubview('edit', $this);
        default:
          return '';
      }
    }

    private function collectionList() {
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
          'id' => "pagesTable",
          'th' => array(
              Utils::tableCell(i('Name')),
              Utils::tableCell(i('Author')),
              Utils::tableCell(i('Created')),
              Utils::tableCell(i('status')),
              Utils::tableCell(i('Actions'))
          ),
          'td' => $this->getPageRows()
      ));
    }

    private function getPageRows($parent=0, $level=0) {
      $rows = array();
      foreach($this->collection->items() as $item) {
        $user = new User($item['author']);
        array_push($rows, array(
          Utils::tableCell($item['name']),
          Utils::tableCell($user->get('username')),
          Utils::tableCell(Utils::dateFormat($item['created'])),
          Utils::tableCell(i($item['status'])),
          Utils::tableCell($this->actions($item))
        ));
      }
      return $rows;
    }

    private function actions($item) {
      $actions = array(
        array(
            "url" => Utils::getUrl(array("manage", "collections", $this->collection->getPref('name'), 'edit', $item['id'])),
            "icon" => "pencil",
            "name" => sprintf(i('edit %s'), $this->collection->getPref('single-item')),
            "ajax" => false,
            "confirm" => false
        )
      );
      if(Auth::allowed($this->permissions["delete"])) {
        array_push($actions, array(
            "url" => Utils::getUrl(array("manage", "collections", $this->collection->getPref('name'), 'delete', $item['id'])),
            "icon" => "remove",
            "name" => sprintf(i('delete %s'), $this->collection->getPref('single-item')),
            "ajax" => true,
            "confirm" => true
        ));
      }
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
          'actions' => $actions
      ));
    }
}

?>
