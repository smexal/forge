<?php

namespace Forge\Core\Views\Manage\Groups;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Group;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class GroupsView extends View {
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
        return $this->app->render(CORE_TEMPLATE_DIR."views/", "groups", array(
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
        return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
            'id' => 'groupsTable',
            'th' => array(
                Utils::tableCell(i('id')),
                Utils::tableCell(i('Name')),
                Utils::tableCell(i('Members')),
                Utils::tableCell(i('Actions'))
            ),
            'td' => $this->getGroupRows()
        ));
    }

    public function getGroupRows() {
        $groups = $this->app->db->get('groups');
        $groups_enriched = array();
        foreach($groups as $group) {
            $obj = new Group($group['id']);
            array_push($groups_enriched, array(
                Utils::tableCell($group['id']),
                Utils::tableCell($group['name']),
                Utils::tableCell($obj->memberCount()),
                Utils::tableCell($this->actions($obj))
            ));
        }
        return $groups_enriched;
    }

    public function actions($group) {
        return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
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

