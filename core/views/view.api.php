<?php

class ApiView extends AbstractView {
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
        default:
          return json_encode(array("Unknown Object Query" => $part));
      }
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
