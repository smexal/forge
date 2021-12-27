<?php

namespace Forge\Core\Views\Manage\Users;

use Forge\Core\Abstracts\View;
use Forge\Core\App\Auth;
use Forge\Core\Classes\Pagination;
use Forge\Core\Classes\Utils;

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
        if(! array_key_exists('page', $_GET)) {
            $page = 1;
        } else {
            $page = $_GET['page'];
        }
        $this->app->db->get('users');
        $total = $this->app->db->count;
        $pagination = new Pagination($this->app->db->count/PAGINATION_SIZE, $page);
        return $pagination->render().$this->app->render(CORE_TEMPLATE_DIR."assets/", "table", [
            'id' => "userTable",
            'th' => array(
                Utils::tableCell(i('id')),
                Utils::tableCell(i('Username')),
                Utils::tableCell(i('E-Mail')),
                Utils::tableCell(i('Actions'))
            ),
            'td' => $this->getUserRows($page)
        ]).$pagination->render();
    }
    public function getUserRows($page) {
        $users = $this->app->db->get('users', [($page-1)*PAGINATION_SIZE, (($page-1)*PAGINATION_SIZE)+PAGINATION_SIZE]);
        $user_enriched = array();
        foreach($users as $user) {
            $row = new \stdClass();
            $row->tds = array(
                Utils::tableCell($user['id']),
                Utils::tableCell($user['username']),
                Utils::tableCell($user['email']),
                Utils::tableCell($this->actions($user['id']), false, false, false, Utils::url(["manage", "users", "edit", $user['id']]))
            );
            $row->rowAction = Utils::getUrl(['manage', 'users', 'edit', $user['id']]);

            array_push($user_enriched, $row);
        }
        return $user_enriched;
    }

    private function actions($id) {
        return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
            'actions' => array(
                array(
                    "url" => Utils::getUrl(array("manage", "users", "delete", $id)),
                    "icon" => "delete",
                    "name" => i('delete user'),
                    "ajax" => true,
                    "confirm" => true
                ),
                array(
                    "url" => Utils::getUrl(array("manage", "users", "edit", $id)),
                    "icon" => "mode_edit",
                    "name" => i('edit user'),
                    "ajax" => true,
                    "confirm" => true
                )
            )
        ));
    }
}
