<?php

namespace Forge\Core\Views;

use Forge\Core\Abstracts as Abstracts;

class ApiView extends Abstracts\View {
    public $name = 'api';
    public $permission = null;
    public $standalone = true;

    public function content($query=array()) {
      header('Content-Type: application/json');

      $part = array_shift($query);
      switch($part) {
        case 'users':
            return $this->users($query);
        case 'pages':
            return $this->pages($query);
        case 'media':
            return $this->media($query);
        case 'navigation-items':
            return $this->navigationItems();
        case 'edit-navigation-item-additional-form':
            return $this->additionalNavigationItemForm($query);
        default:
          $return = API::instance()->run($part, $query, $_POST);
          if($return) {
            return $return;
          } else {
            return json_encode(array("Unknown Object Query" => $part));
          }
      }
    }

    private function additionalNavigationItemForm($query) {
      $v = App::instance()->vm->getViewByName($query[0]);
      return json_encode($v->additionalNavigationForm());
    }

    private function media($query) {
        $mediamanager = new MediaManager();
        if($query[0] == 'upload') {
            $mediamanager->create($_FILES['file']);
        }
    }

    private function pages($query) {
      if(count($query) == 0) {
        // no information about a specific page is requred. return all.
        return json_encode(Pages::getAll());
      } else {
        if($query[0] == 'search') {
          return json_encode(Pages::search($query[1]));
        }
      }
    }

    private function users($query) {
      if(count($query) == 0) {
        // no information about a specific user is requred. return all.
        return json_encode(User::getAll());
      } else {
        if($query[0] == 'search') {
          return json_encode(User::search($query[1]));
        }
      }
    }
}


?>
