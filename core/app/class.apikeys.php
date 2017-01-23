<?php

namespace Forge\Core\App;

use \Forge\Core\App\App;
use \Forge\Core\Classes\User;

class APIKeys {

    public function __construct() {

    }

    public function create($user = null) {
        if(is_null($user)) {
            $user = App::instance()->user;
        }
        $data = [
            'user' => $user->get('id'),
            'keey' => $this->buildOne($user),
            'description' => ''
        ];
        App::instance()->db->insert('api_keys', $data);
    }

    public function getAll($user = null) {
        if(is_null($user)) {
            $user = App::instance()->user;
        }
        $keys = array();
        $db = App::instance()->db;
        $db->where('user', $user->get('id'));
        $keys = $db->get('api_keys');

        return $keys;
    }

    public function buildOne($user) {
        return md5(microtime(true).$user->get('id'));
    }

}