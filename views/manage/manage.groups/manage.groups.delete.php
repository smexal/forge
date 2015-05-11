<?php

class ManageDeleteGroup extends AbstractView {
    public $parent = 'groups';
    public $permission = 'manage.groups.delete';
    public $name = 'delete';

    public function content($uri=array()) {
        if(is_array($uri)) {
            if($uri[0] == "cancel") {
                App::instance()->redirect(Utils::getUrl(array('manage', 'groups')));
            }
            $id = $uri[0];
            if(count($uri) > 1) {
                if($uri[1] == 'confirmed') {
                    // delete user
                    if(($message = Group::delete($id)) === true) {
                        // success
                        App::instance()->addMessage(i('Group has been deleted.'), "success");
                    } else {
                        // failed
                        App::instance()->addMessage(i($message), "danger");
                    }
                    App::instance()->redirect(Utils::getUrl(array('manage', 'groups')));
                }
            } else {
                return $this->confirmationScreen($id);
            }
        }
    }

    private function confirmationScreen($id) {
      // display confirm screen;
      $group = new Group($id);
      return $this->app->render(TEMPLATE_DIR."assets/", "confirm", array(
          "title" => sprintf(i('Delete groups \'%s\'?'), $group->get('name')),
          "message" => sprintf(i('Do you really want the group with the name \'%s\'?'), $group->get('name')),
          "yes" => array(
              "title" => i('Yes, delete group'),
              "url" => Utils::getUrl(array("manage", "groups", "delete", $id, "confirmed"))
          ),
          "no" => array(
              "title" => i("No, cancel."),
              "url" => Utils::getUrl(array("manage", "groups", "delete", "cancel"))
          )
      ));
    }
}

?>
