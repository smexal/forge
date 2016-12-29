<?php

namespace Forge\Core\Classes;

class Group {
    private $app = null;
    public $id = null;
    private $fields = array(
      "id",
      "name"
    );

    public function __construct($id) {
        if(is_numeric($id)) {
            $this->app = App::instance();
            $this->id = $id;
        } else {
            Logger::error("Tried to instance of Group with id '".$id."'");
        }
    }

    /**
     * Returns the value for a given field.
     * @param  String $field Field name
     * @return String        Value of the required field
     */
    public function get($field) {
      if(in_array($field, $this->fields)) {
        $this->app->db->where("id", $this->id);
        $group = $this->app->db->getOne("groups");
        return $group[$field];
      }
    }

    public function memberCount() {
        $this->app->db->where("groupid", $this->id);
        $this->app->db->get("groups_users");
        return $this->app->db->count;
    }

    public function members() {
      $this->app->db->where("groupid", $this->id);
      $members = $this->app->db->get("groups_users");
      $id_array = array();
      foreach($members as $member) {
        array_push($id_array, $member['userid']);
      }
      return $id_array;
    }

    public function addMembers($members) {
      if(!Auth::allowed("manage.groups.members")) {
        return;
      }
      if(is_array($members)) {
        $current = $this->members();
        foreach($members as $member) {
          if(!in_array($member, $current) && User::exists($member)) {
            $this->app->db->insert("groups_users", array(
                "groupid" => $this->get('id'),
                "userid" => $member
            ));
          } else {
            Logger::info("Skipped to add User ". $member . " to add to group with id ". $this->get('id') . ", reason: duplicate");
          }
        }
      } else {
        throw new Exception("add Members requires an array of new members.");
      }
    }

    public function setName($newName) {
      if($newName == $this->get('name')) {
        // nothing to change...
        return true;
      }
      if(strlen($newName) < 3) {
        return i('The given group name is too short.');
      }
      if(Group::exists($newName)) {
        return i('A group with that name already exists.');
      }
      $app = App::instance();
      $app->db->where("id", $this->get('id'));
      $app->db->update("groups", array(
        "name" => $newName
      ));
      return true;
    }

    public function grant($permission) {
      if(! self::hasPermission($this->id, $permission)) {
        $this->app->db->insert('permissions_groups', array(
            "groupid" => $this->id,
            "permissionid" => $permission
        ));
        return true;
      }
    }

    public function deny($permission) {
      $this->app->db->where("groupid", $this->id);
      $this->app->db->where("permissionid", $permission);
      $this->app->db->delete("permissions_groups");
      return true;
    }

    public static function exists($name) {
      $app = App::instance();
      $app->db->where('name', $name);
      $app->db->get('groups');
      if($app->db->count > 0) {
        return true;
      }
      return false;
    }

    /**
     * Creates a new Group in the database, if there is no
     * group with this name already in the database.
     * @param  String $name name of the new group
     * @return String/Boolean boolean true, when ok. Error Message in String form.
     */
    public static function create($name) {
      $app = App::instance();
      if(!Group::exists($name)) {
        $app->db->insert('groups', array(
          "name" => $name
        ));
        return true;
      } else {
        return i("A group with that name already exists.");
      }
    }

    public static function getAll() {
      return App::instance()->db->get('groups');
    }

    /**
     * deletes a group, its members and permissions
     * @param  Integer $id Group ID
     * @return Boolean     Success = True, Failure = False
     */
    public static function delete($id) {
      if(! Auth::allowed("manage.groups.delete")) {
        return i('Permission denied');
      }
      $app = App::instance();
      // check if the current user is in this group... then no delete.
      if($app->user->hasGroup($id)) {
        return i('Unable to delete a group, where the current user is in.');
      }

      // delete the groups users.
      $app->db->where("groupid", $id);
      $app->db->delete("groups_users");

      // delete this groups permissions
      $app->db->where("groupid", $id);
      $app->db->delete("permissions_groups");

      // delete the group itself.
      $app->db->where("id", $id);
      $app->db->delete("groups");

      return true;
    }

    public static function hasPermission($group, $permission) {
      $db = App::instance()->db;
      $db->where("groupid", $group);
      $db->where("permissionid", $permission);
      if(count($db->get("permissions_groups")) > 0) {
        return true;
      }
      return false;
    }

}

?>
