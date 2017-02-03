<?php

namespace Forge\Core\App;

use \Forge\Core\App\App;
use \Forge\Core\Classes\User;

use \Forge\Core\Classes\Logger;

class APIKeys {

    public function __construct() {

    }

    public function toggle($key, $permission) {
        if(self::allowed($key, $permission)) {
            // remove permission
            App::instance()->db->where('permission', $permission);
            App::instance()->db->where('keey', $key);
            App::instance()->db->delete('permissions_keys');
        } else {
            // add permission
            $data = [
                'permission' => $permission,
                'keey' => $key
            ];
            App::instance()->db->insert("permissions_keys", $data);
        }
    }

    public static function allowed($key, $permission) {
        $keyId = null;
        // entering at this with a key id, this is only allowed, when logged in.
        // that's why this gets checked now.
        if(is_numeric($key)) {
            // get key value but only if logged in as user
            $keys = new self;
            foreach($keys->getAll() as $k) {
                if($k['id'] == $key) {
                    $keyId = $key;
                    $key = $k['keey'];
                }
            }
            if(is_numeric($key)) {
                return;
            }
        }

        // get key id if we dont have one already.
        if(is_null($keyId)) {
            App::instance()->db->where('keey', $key);
            $key = App::instance()->db->getOne("api_keys");
            $keyId = $key['id'];
        }

        // check if the permission is numeric, if not, let's get the id of it.
        if(! is_numeric($permission)) {
            App::instance()->db->where('name', $permission);
            $permission = App::instance()->db->getOne('permissions');
            $permission = $permission['id'];
        }

        // check if permission exists
        App::instance()->db->where('permission', $permission);
        App::instance()->db->where('keey', $keyId);
        App::instance()->db->get("permissions_keys");
        $result = App::instance()->db->count;
        if($result > 0) {
            return true;
        }
        return false;
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

    public function deleteKey($id) {
        // check if it's a key from the current user.
        foreach($this->getAll() as $key) {
            if($key['id'] == $id) {
                // delete, because it is the user's key.
                $db = App::instance()->db;
                $db->where('id', $id);
                $db->delete('api_keys');
                return true;
            }
        }
        return false;
    }

    public function buildOne($user) {
        return md5(microtime(true).$user->get('id'));
    }

}