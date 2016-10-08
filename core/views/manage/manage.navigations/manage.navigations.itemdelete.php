<?php

class ManageNavigationsItemDelete extends AbstractView {
    public $parent = 'navigation';
    public $permission = 'manage.navigations.delete';
    public $name = 'itemdelete';

    public function content($uri=array()) {
        if(is_array($uri)) {
            if($uri[0] == "cancel") {
                App::instance()->redirect(Utils::getUrl(array('manage', 'navigation')));
            }
            $id = $uri[0];
            if(count($uri) > 1) {
                if($uri[1] == 'confirmed') {
                    // delete navigation item
                    if(ContentNavigation::deleteItem($id)) {
                        // success
                        App::instance()->addMessage(i('Navigation Item has been deleted.'), "success");
                    } else {
                        // failed
                        App::instance()->addMessage(i('There was an error while deleting the navigation item.'), "danger");
                    }
                    App::instance()->redirect(Utils::getUrl(array('manage', 'navigation')));
                }
            } else {
                return $this->confirmationScreen($id);
            }
        }
    }

    private function confirmationScreen($id) {
      // display confirm screen;
      $item = ContentNavigation::getItem($id);
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "confirm", array(
          "title" => sprintf(i('Delete navigation item \'%s\'?'), $item['name']),
          "message" => sprintf(i('Do you really want to delete the navigation item with the name \'%s\'?'), $item['name']),
          "yes" => array(
              "title" => i('Yes, delete item'),
              "url" => Utils::getUrl(array("manage", "navigation", "itemdelete", $id, "confirmed"))
          ),
          "no" => array(
              "title" => i("No, cancel."),
              "url" => Utils::getUrl(array("manage", "navigation", "itemdelete", "cancel"))
          )
      ));
    }
}

?>
