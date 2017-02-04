<?php

namespace Forge\Core\Views\Manage\Users;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class UsersView extends View {
    public $parent = 'manage';
    public $permission = 'manage.users';
    public $name = 'users';
    public $permissions = array(
        0 => 'manage.users.add'
    );

    public function content($uri=array()) {
        if(count($uri) > 0 && Auth::allowed($this->permissions[0])) {
          return $this->getSubview($uri, $this);
        } else {
          return $this->ownContent();
        }
    }
    private function ownContent() {
        return $this->app->render(CORE_TEMPLATE_DIR."views/", "users", array(
            'title' => i('User Management'),
            'new_user' => i('Add user'),
            'add_permission' => Auth::allowed($this->permissions[0]),
            'table' => $this->userTable(),
            'add_url' => Utils::getUrl(array('manage', 'users', 'add'))
        ));
    }

    public function userTable() {
        return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
            'id' => "userTable",
            'th' => array(
                Utils::tableCell(i('id')),
                Utils::tableCell(i('Username')),
                Utils::tableCell(i('E-Mail')),
                Utils::tableCell(i('Actions'))
            ),
            'td' => $this->getUserRows()
        ));
    }
    public function getUserRows() {
        $users = $this->app->db->get('users');
        $user_enriched = array();
        foreach($users as $user) {
            array_push($user_enriched, array(
                Utils::tableCell($user['id']),
                Utils::tableCell($user['username']),
                Utils::tableCell($user['email']),
                Utils::tableCell($this->actions($user['id']))
            ));
        }
        return $user_enriched;
    }

    private function actions($id) {
        return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
            'actions' => array(
                array(
                    "url" => Utils::getUrl(array("manage", "users", "delete", $id)),
                    "icon" => "remove",
                    "name" => i('delete user'),
                    "ajax" => true,
                    "confirm" => true
                ),
                array(
                    "url" => Utils::getUrl(array("manage", "users", "edit", $id)),
                    "icon" => "pencil",
                    "name" => i('edit user'),
                    "ajax" => true,
                    "confirm" => true
                )
            )
        ));
    }
}

?>
