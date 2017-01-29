<?php

namespace Forge\Core\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\Auth;
use \Forge\Core\App\ModifyHandler;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class CollectionManagement extends View {
    public $parent = 'manage';
    public $name = 'collections';
    public $permission = 'manage.collections';
    public $permissions = array(
      'add' => "manage.collections.add",
      'configure' => "manage.collections.configure",
      'categories' => "manage.collections.categories",
      'delete' => "manage.collections.delete"
    );

    private $collection = false;

    public function content($uri=array()) {
      if (empty($uri)) {
        return $this->app->redirect("404");
      }

      // find out which collection we are editing
      foreach ($this->app->cm->collections as $collection) {
        if ($collection::$name == $uri[0]) {
          $this->collection = $collection::instance();
          break;
        }
      }

      if (is_null($this->collection)) {
        $this->app->redirect("404");
      }

      // check if user has permission
      if (!Auth::allowed($this->collection->getPermission())) {
        $this->app->redirect("denied");
      }
      
      // render subview
      if (count($uri) > 1) {
        return $this->subviews($uri);
        // render the overview
      } else {
        $global_actions = '';

        if(Auth::allowed($this->permissions['add'])) {
          $global_actions = $this->app->render(CORE_TEMPLATE_DIR."assets/", "overlay-button", array(
            'url' => Utils::getUrl(array('manage', 'collections', $this->collection->getName(), 'add')),
            'label' => $this->collection->getPref('add-label')
          ));
        }

        if (Auth::allowed($this->permissions['configure'])) {
          $global_actions.= $this->app->render(CORE_TEMPLATE_DIR."assets/", "overlay-button", array(
            'url' => Utils::getUrl(array('manage', 'collections', $this->collection->getName(), 'configure')),
            'label' => i('Configure', 'core')
          ));
        }

        if (Auth::allowed($this->permissions['categories'])) {
          $global_actions.= $this->app->render(CORE_TEMPLATE_DIR."assets/", "overlay-button", array(
            'url' => Utils::getUrl(array('manage', 'collections', $this->collection->getName(), 'categories')),
            'label' => i('Categories', 'core')
          ));
        }

        return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
            'title' => $this->collection->getPref('all-title'),
            'global_actions' => $global_actions,
            'content' => $this->collectionList()
        ));
      }
    }

    private function subviews($uri) {
      switch ($uri[1]) {
        case 'add':
          return $this->getSubview('add', $this);
        case 'delete':
          return $this->getSubview('delete', $this);
        case 'edit':
          return $this->getSubview('edit', $this);
        case 'categories':
          return $this->getSubview('categories', $this);
        case 'configure':
          return $this->getSubview('configure', $this);
        default:
          return '';
      }
    }

    private function collectionList() {
      $headings = [
              Utils::tableCell(i('Name')),
              Utils::tableCell(i('Author')),
              Utils::tableCell(i('Created')),
              Utils::tableCell(i('status')),
              Utils::tableCell(i('Actions'))
      ];

      $headings = ModifyHandler::instance()->trigger('ForgeCore_CollectionManagement_HeaderList', $headings);

      $table = [
          'id' => "pagesTable",
          'th' => $headings,
          'td' => $this->getPageRows()
      ];

      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", $table);
    }

    private function getPageRows($parent=0, $level=0) {
      $rows = array();

      foreach ($this->collection->items() as $item) {
        error_log("TEST");
        $user = new User($item->getAuthor());
        array_push($rows, array(
          Utils::tableCell(
            $this->app->render(CORE_TEMPLATE_DIR."assets/", "a", array(
                "href" => Utils::getUrl(array("manage", "collections", $this->collection->getName(), 'edit', $item->id)),
                "name" => $item->getName()
            ))
          ),
          Utils::tableCell($user->get('username')),
          Utils::tableCell(Utils::dateFormat($item->getCreationDate())),
          Utils::tableCell(i($item->getMeta('status'))),
          Utils::tableCell($this->actions($item))
        ));
      }

      $rows = $rows; // filter

      return $rows;
    }

    private function actions($item) {
      $actions = array(
        array(
            "url" => Utils::getUrl(array("manage", "collections", $this->collection->getName(), 'edit', $item->id)),
            "icon" => "pencil",
            "name" => sprintf(i('edit %s'), $this->collection->getPref('single-item')),
            "ajax" => false,
            "confirm" => false
        )
      );
      if (Auth::allowed($this->permissions["delete"])) {
        array_push($actions, array(
            "url" => Utils::getUrl(array("manage", "collections", $this->collection->getName(), 'delete', $item->id)),
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
