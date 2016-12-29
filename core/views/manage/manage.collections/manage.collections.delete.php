<?php

namespace Forge\Core\Views;

use Forge\Core\Abstracts as Abstracts;

class CollectionManagementDelete extends Abstracts\View {
    public $parent = 'collections';
    public $name = 'delete';
    public $permission = 'manage.collections.delete';

    public function content($uri=array()) {
        if(is_array($uri)) {
            if($uri[1] == "cancel") {
                $uri = Utils::getUriComponents();
                array_pop($uri);
                array_pop($uri);
                App::instance()->redirect(Utils::getUrl($uri));
            }
            $id = $uri[1];
            if(count($uri) > 2) {
                if($uri[2] == 'confirmed') {
                    // delete collection-item
                    App::instance()->cm->deleteCollectionItem($id);

                    $uri = Utils::getUriComponents();
                    array_pop($uri);
                    array_pop($uri);
                    array_pop($uri);
                    App::instance()->redirect(Utils::getUrl($uri));
                }
            } else {
                return $this->confirmationScreen($id);
            }
        }
    }

    private function confirmationScreen($id) {
      // display confirm screen;
      $db = App::instance()->db;
      $db->where('id', $id);
      $item = $db->getOne('collections');
      $base_uri = Utils::getUriComponents();
      array_pop($base_uri);
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "confirm", array(
          "title" => sprintf(i('Delete item \'%s\'?'), $item['name']),
          "message" => sprintf(i('Do you really want to delete this item?')),
          "yes" => array(
              "title" => i('Yes, delete item'),
              "url" => Utils::getUrl(array_merge($base_uri, array($id, "confirmed")))
          ),
          "no" => array(
              "title" => i("No, cancel."),
              "url" => Utils::getUrl(array_merge($base_uri, array("cancel")))
          )
      ));
    }

}

?>
