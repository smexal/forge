<?php 

class UserManagement extends AbstractView {
    public $parent = 'manage';
    public $permission = 'manage.users.display';
    public $default = true;
    public $name = 'users';

    public function content() {
        return $this->app->render(TEMPLATE_DIR."views/", "users", array(
            'title' => i('User Management'),
            'new_user' => i('New user'),
            'table' => $this->userTable()
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