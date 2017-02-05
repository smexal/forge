<?php

namespace Forge\Core\Views\Manage\Permissions;

use \Forge\Core\Abstracts\View;
use \Forge\Core\Classes\Group;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class PermissionView extends View {
    public $parent = 'manage';
    public $name = 'permissions';
    public $permission = 'manage.permissions';
    private $permissionTableId = 'permissionTable';
    public $permissions = array(
    );

    public function content($uri=array()) {
      if(count($uri) == 3) {
        $groupid = $uri[1];
        $permission = $uri[2];
        if(!is_numeric($groupid) && ! is_numeric($permission)) {
          // look strange, just return the own content...
          return $this->ownContent();
        } else {
          $group = new Group($groupid);
        }
        if($uri[0] == "deny") {
          // remove a permission
          $group->deny($permission);
        }
        if($uri[0] == "grant") {
          // grant a permission
          $group->grant($permission);
        }
        $this->app->refresh($this->cellId($permission, $group->id), $this->getCell($group->id, $permission, true));
      } else {
        return $this->ownContent();
      }
    }

    public function ownContent() {
      return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
        'title' => i('Permission Management'),
        'content' => $this->permissionTable(),
        'global_actions' => false
      ));
    }

    public function permissionTable() {
      $groups = $this->getGroups();
      $permissions = $this->getPermissions();
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
        'id' => $this->permissionTableId,
        'th' => array_merge(array(Utils::tableCell(i('Permission'))), $this->getGroupNames($groups)),
        'td' => $this->getRows($permissions, $groups)
      ));
    }

    private function getPermissions() {
      $this->app->db->orderBy("name", "asc");
      return $this->app->db->get("permissions");
    }

    private function getGroups() {
      return $this->app->db->get("groups");
    }

    private function getRows($permissions, $groups) {
      $rows = array();
      foreach($permissions as $permission) {
        array_push($rows, array_merge(
            array(Utils::tableCell($permission['name'])),
            $this->getGroupsPermission($groups, $permission['id'])
        ));
      }
      return $rows;
    }

    private function getGroupsPermission($groups, $permission) {
      $cells = array();
      foreach($groups as $group) {
        array_push($cells, $this->getCell($group['id'], $permission));
      }
      return $cells;
    }

    private function getCell($group, $permission, $structure=false) {
      if(Group::hasPermission($group, $permission)) {
        return Utils::tableCell($this->action($group, "deny", $permission), "center", $this->cellId($permission, $group), $structure);
      } else {
        return Utils::tableCell($this->action($group, "grant", $permission), "center", $this->cellId($permission, $group), $structure);
      }
    }

    private function action($groupid, $type, $permission) {
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
          'actions' => array(
              array(
                  "url" => Utils::getUrl(array("manage", "permissions", $type, $groupid, $permission)),
                  "icon" => $type == "grant" ? "unchecked" : "ok",
                  "name" => $type == "grant" ? i('Add Permission') : i('Remove Permission'),
                  "ajax" => true,
                  "confirm" => false
              )
          )
      ));
    }

    private function cellId($permission, $group) {
      return "perm-".$permission."--gr-".$group;
    }

    private function getPermissionNames($permissions) {
      $names = array();
      foreach($permissions as $permission) {
        array_push($names, $permission['name']);
      }
      return $names;
    }

    private function getGroupNames($groups) {
      $names = array();
      foreach($groups as $group) {
        array_push($names, Utils::tableCell($group['name'], "center width-200"));
      }
      return $names;
    }
}

