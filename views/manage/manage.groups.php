<?php

class GroupsManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'groups';
    public $permission = 'manage.groups';
    public $permissions = array(
        'manage.groups.add'
    );

    public function content($uri=array()) {
      if(count($uri) > 0) {
            return $this->getSubview($uri, $this);
        } else {
            return $this->ownContent();
        }
    }

    public function ownContent() {
        return $this->app->render(TEMPLATE_DIR."views/", "groups", array(
            'title' => i('Group Management'),
            'add' => array(
                "permission" => Auth::allowed($this->permissions[0]),
                "title" => i('Add new Group'),
                "url" => Utils::getUrl(array("manage", "groups", "add"))
            ),
            'table' => $this->groupTable()
        ));
    }

    public function groupTable() {
        return $this->app->render(TEMPLATE_DIR."assets/", "table", array(
            'th' => array(i('id'), i('Name'), i('Members'), i('Actions')),
            'td' => $this->getGroupRows()
        ));
    }

    public function getGroupRows() {
        $groups = $this->app->db->get('groups');
        $groups_enriched = array();
        foreach($groups as $group) {
            $obj = new Group($group['id']);
            array_push($groups_enriched, array(
                $group['id'],
                $group['name'],
                $obj->memberCount(),
                $this->actions($obj)
            ));
        }
        return $groups_enriched;
    }

    public function actions($group) {
        return $this->app->render(TEMPLATE_DIR."assets/", "table.actions", array(
            'actions' => array(
                array(
                    "url" => Utils::getUrl(array("manage", "groups", "edit", $group->id)),
                    "icon" => "pencil",
                    "name" => i('edit group'),
                    "ajax" => true,
                    "confirm" => true
                ),
                array(
                    "url" => Utils::getUrl(array("manage", "groups", "delete", $group->id)),
                    "icon" => "remove",
                    "name" => i('delete group'),
                    "ajax" => true,
                    "confirm" => true
                ),
                array(
                    "url" => Utils::getUrl(array("manage", "groups", "members", $group->id)),
                    "icon" => "user",
                    "name" => i('manage members'),
                    "ajax" => true,
                    "confirm" => true
                )
            )
        ));
    }
}

?>
