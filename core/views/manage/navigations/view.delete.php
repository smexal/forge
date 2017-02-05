<?php

namespace Forge\Core\Views\Manage\Navigations;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\ContentNavigation;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class DeleteView extends View {
    public $parent = 'navigation';
    public $permission = 'manage.navigations.delete';
    public $name = 'delete';

    public function content($uri=array()) {
        if(is_array($uri)) {
            if($uri[0] == "cancel") {
                App::instance()->redirect(Utils::getUrl(array('manage', 'navigation')));
            }
            $id = $uri[0];
            if(count($uri) > 1) {
                if($uri[1] == 'confirmed') {
                    // delete navigation item
                    if(ContentNavigation::delete($id)) {
                        // success
                        App::instance()->addMessage(i('Navigation has been deleted.'), "success");
                    } else {
                        // failed
                        App::instance()->addMessage(i('There was an error while deleting the navigation.'), "danger");
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
      $navigation = ContentNavigation::getById($id);
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "confirm", array(
          "title" => sprintf(i('Delete navigation \'%s\'?'), $navigation['name']),
          "message" => sprintf(i('Do you really want to delete the navigation and all its items?'), $navigation['name']),
          "yes" => array(
              "title" => i('Yes, delete entire navigation'),
              "url" => Utils::getUrl(array("manage", "navigation", "delete", $id, "confirmed"))
          ),
          "no" => array(
              "title" => i("No, cancel."),
              "url" => Utils::getUrl(array("manage", "navigation", "delete", "cancel"))
          )
      ));
    }
}

