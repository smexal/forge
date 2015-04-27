<?php 

class UserManagement extends AbstractView {
    public $parent = 'manage';
    public $permission = 'manage.users.display';
    public $default = true;
    public $name = 'users';
    public $permissions = array(
        0 => 'manage.users.add'
    );

    public function content() {
        return $this->app->render(TEMPLATE_DIR."views/", "users", array(
            'title' => i('User Management'),
            'new_user' => i('Add user'),
            'add_permission' => Auth::allowed($this->permissions[0]),
            'table' => $this->userTable(),
            'add_url' => Utils::getUrl(array('manage', 'users', 'add'))
        ));
    }

    public function userTable() {
        return $this->app->render(TEMPLATE_DIR."assets/", "table", array(
            'th' => array(i('Username'), i('E-Mail'), i('Actions')),
            'td' => $this->getUserRows()
        ));
    }
    public function getUserRows() {
        $users = $this->app->db->get('users');
        $user_enriched = array();
        foreach($users as $user) {
            array_push($user_enriched, array(
                $user['username'],
                $user['email'],
                'actions'
            ));
        }
        return $user_enriched;
    }
}

?>