<?php

class User {
    private $app;
    private $data = false;

    public function __construct($id) {
        $this->app = App::instance();
        $this->groups();
        $this->data['id'] = $id;
    }

    public function get($field) {
        if(array_key_exists($field, $this->data))
            return $this->data[$field];
    }

    public function groups() {
        $this->app->db->where('userid', $this->data['id']);
        $this->data['groups'] = $this->app->db->get('groups_users');
    }

    public function allowed($permission) {
        if(array_key_exists('permissions', $this->data) && array_key_exists($permission, $this->data['permissions'])) {
            return $this->data['permissions'][$permission];
        }
        $this->app->db->where('name', $permission);
        $permission = $this->app->db->getOne('permissions');
        $this->app->db->where('permissionid', $permission['id']);
        $groupsWithPermission = $this->app->db->get('permissions_groups');
        foreach($this->data['groups'] as $user_group) {
            foreach($groupsWithPermission as $db_group) {
                if($user_group['groupid'] == $db_group['groupid']) {
                    $this->data['permissions'][$permission['name']] = true;
                    return true;
                }
            }
        }
        $this->data['permissions'][$permission['name']] = false;
        return false;
    }

    public static function create($name, $password, $email) {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return i('Invalid e-mail address.');
        }
        if(strlen($password) <= 3) {
            return i('Given password is too short.');
        }
        if(strlen($name) <= 2) {
            return i('Username is too short.');
        }
        $app = App::instance();
        $app->db->where("username", $name);
        $app->db->get("users");
        if($app->db->count > 0) {
            return i('User with that name already exists');
        }
        $app->db->where("email", $email);
        $app->db->get("users");        
        if($app->db->count > 0) {
            return i('User with that email already exists');
        }

        $data = array(
            'username' => $name,
            'password' => Utils::password($password), 
            'email' => $email
        );
        $app->db->insert('users', $data);
        return false;
    }

}

?>