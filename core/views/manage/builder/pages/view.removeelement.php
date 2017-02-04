<?php

namespace Forge\Core\Views\Manage\Builder;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class RemoveElementView extends View {
    public $parent = 'pages';
    public $permission = 'manage.builder.pages.edit';
    public $name = 'remove-element';

    public function content($uri = array()) {
        if(is_array($uri)) {
            $id = $uri[0];
            if(count($uri) > 1) {
                $component = App::instance()->com->instance($id);
                $page_id = $component->getPage();
                if($uri[1] == "cancel") {
                    App::instance()->redirect(Utils::getUrl(array('manage', 'pages', 'edit', $page_id)));
                } else if ($uri[1] == 'confirmed') {
                    if(App::instance()->com->deleteComponent($id)) {
                        App::instance()->addMessage(i('Component removed.'), "success");
                    } else {
                        App::instance()->addMessage(i('Error while removing component.'), "danger");
                    }
                    App::instance()->redirect(Utils::getUrl(array('manage', 'pages', 'edit', $page_id)));
                }
            }
            return $this->confirmationScreen($id);
        }
    }

    private function confirmationScreen($id) {
      // display confirm screen;
      $component = App::instance()->com->instance($id);
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "confirm", array(
          "title" => sprintf(i('Delete component \'%s\'?'), $component->getPref('name')),
          "message" => sprintf(i('Do you really want to delete the component \'%s\' and its content?'), $component->getPref('name')),
          "yes" => array(
              "title" => i('Yes, delete component'),
              "url" => Utils::getUrl(array("manage", "pages", "remove-element", $id, "confirmed"))
          ),
          "no" => array(
              "title" => i("No, cancel."),
              "url" => Utils::getUrl(array("manage", "pages", "remove-element", $id, "cancel"))
          )
      ));
    }
}

?>
