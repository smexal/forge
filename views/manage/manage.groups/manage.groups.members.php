<?php

class ManageGroupMembers extends AbstractView {
    public $parent = 'groups';
    public $permission = 'manage.groups.members';
    public $name = 'members';
    public $message = '';
    private $group = null;
    public $events = array(
      0 => "onAddNewGroupMember"
    );

    public function content($uri=array()) {
      if(is_numeric($uri[0]) && is_null($this->group)) {
        $this->group = new Group($uri[0]);
      }

      return $this->app->render(TEMPLATE_DIR."views/parts/", "group.members", array(
        'title' => sprintf(i('Modify %s\'s Members'), $this->group->get('name')),
        'message' => $this->message,
        'form' => $this->addForm(),
        'memberlist' => $this->getMemberList()
      ));
    }

    public function addForm() {
      $form = new Form(Utils::getUrl(array('manage', 'groups', 'members', $this->group->get('id'))));
      $form->ajax(".content");
      $form->hidden("event", $this->events[0]);
      $form->tags("new_users", "new_users", i('Add Usernames'), array("CHF", "EUR"));
      $form->submit(i('Add'));
      return $form->render();
    }

    public function getMemberList() {
        return $this->app->render(TEMPLATE_DIR."assets/", "table", array(
            'th' => array(i('Username'), i('E-Mail'), i('Remove')),
            'td' => $this->getUserRows()
        ));
    }
    public function getUserRows() {
      $this->app->db->where('groupid', $this->group->get('id'));
      $user_enriched = array();
      foreach($this->group->members() as $userid) {
        $user = new User($userid);
        array_push($user_enriched, array(
          $user->get('username'),
          $user->get('email'),
          $this->actions($user->get('id'))
        ));
      }
      return $user_enriched;
    }

    private function actions($id) {
      return $this->app->render(TEMPLATE_DIR."assets/", "table.actions", array(
        'actions' => array(
          array(
            "url" => Utils::getUrl(array("manage", "groups", "members", $this->group->get('id'), "remove", $id)),
            "icon" => "remove",
            "name" => i('remove user'),
            "ajax" => true,
            "confirm" => false
          )
        )
      ));
    }
}

?>
