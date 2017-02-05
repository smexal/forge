<?php

namespace Forge\Core\Views\Manage\Builder;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Page;
use \Forge\Core\Classes\Pages;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class DeleteView extends View {
    public $parent = 'pages';
    public $permission = 'manage.builder.pages.delete';
    public $name = 'delete';

    public function content($uri=array()) {
      if(is_array($uri)) {
        if($uri[0] == "cancel") {
            App::instance()->redirect(Classes\Utils::getUrl(array('manage', 'pages')));
        }
        $id = $uri[0];
        if(count($uri) > 1) {
          if($uri[1] == 'confirmed') {
            // delete user
            if(Pages::delete($id)) {
              // success
              App::instance()->addMessage(i('Page has been deleted.'), "success");
            } else {
              // failed
              App::instance()->addMessage(i('There was an error while deleting the page.'), "danger");
            }
            App::instance()->redirect(Utils::getUrl(array('manage', 'pages')));
          }
        } else {
            return $this->confirmationScreen($id);
        }
      }
    }

    private function confirmationScreen($id) {
      // display confirm screen;
      $page = new Page($id);
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "confirm", array(
          "title" => sprintf(i('Delete page \'%s\'?'), $page->name),
          "message" => sprintf(i('Do you really want to delete the page \'%s\'?'), $page->name),
          "yes" => array(
              "title" => i('Yes, delete page'),
              "url" => Utils::getUrl(array("manage", "pages", "delete", $id, "confirmed"))
          ),
          "no" => array(
              "title" => i("No, cancel."),
              "url" => Utils::getUrl(array("manage", "pages", "delete", "cancel"))
          )
      ));
    }
}

