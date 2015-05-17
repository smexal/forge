<?php

class ManageGroupMembers extends AbstractView {
    public $parent = 'groups';
    public $permission = 'manage.groups.members';
    public $name = 'members';
    public $message = '';
    private $group = null;
    private $memberTableId = "groupMemberList";
    public $events = array(
      0 => "onAddNewGroupMember"
    );

    public function content($uri=array()) {
      if(is_numeric($uri[0]) && is_null($this->group)) {
        $this->group = new Group($uri[0]);
      }
      
      if(count($uri) > 1) {
        if($uri[1] === 'remove' && is_numeric($uri[2]) && User::exists($uri[2])) {
          // remove user from group.
          $userid = $uri[2];
          $this->app->db->where('groupid', $this->group->get('id'));
          $this->app->db->where('userid', $userid);
          $this->app->db->delete('groups_users');
          $this->app->refresh($this->memberTableId, $this->getMemberList());
        }
      }

      return $this->app->render(TEMPLATE_DIR."views/parts/", "group.members", array(
        'title' => sprintf(i('Modify %s\'s Members'), $this->group->get('name')),
        'message' => $this->message,
        'form' => $this->addForm(),
        'memberlist' => $this->getMemberList()
      ));
    }
    
    public function onAddNewGroupMember($data) {
      $members = explode(",", $data['new_users']);
      if(count($members) > 0) {
        $group = new Group($data['groupid']);
        $group->addMembers($members);
      }
    }

    public function addForm() {
      $form = new Form(Utils::getUrl(array('manage', 'groups', 'members', $this->group->get('id'))));
      $form->ajax(".content");
      $form->hidden("event", $this->events[0]);
      $form->hidden("groupid", $this->group->get('id'));
      $form->tags("new_users", "new_users", i('Add Users by typing their username:'), false, array(
          "value" => "id",
          "name" => "username",
          "url" => Utils::getUrl(array("api", "users"))
      ));
      $form->submit(i('Add'));
      return $form->render();
    }

    public function getMemberList() {
        return $this->app->render(TEMPLATE_DIR."assets/", "table", array(
            'id' => $this->memberTableId,
            'th' => array(
                Utils::tableCell(i('Username')),
                Utils::tableCell(i('E-Mail')),
                Utils::tableCell(i('Remove'))
            ),
            'td' => $this->getUserRows()
        ));
    }
    public function getUserRows() {
      $this->app->db->where('groupid', $this->group->get('id'));
      $user_enriched = array();
      foreach($this->group->members() as $userid) {
        $user = new User($userid);
        array_push($user_enriched, array(
          Utils::tableCell($user->get('username')),
          Utils::tableCell($user->get('email')),
          Utils::tableCell($this->actions($user->get('id')))
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
