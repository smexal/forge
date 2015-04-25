<?php

class Auth {
    public static function any() {
        if(isset($_SESSION['auth']) && is_numeric($_SESSION['auth'])) {
            return true;
        } else {
            return false;
        }
    }
    public static function setSessionUser() {
        if(Auth::any())
            App::instance()->user = new User($_SESSION['auth']);
    }    

    public static function destroy() {
        App::instance()->user = null;
        unset($_SESSION['auth']);
    }

    public static function allowed($permission) {
        // not even logged in... send to login
        if(! Auth::any() || is_null(App::instance()->user)) {
            App::instance()->redirect('login');
        }
        return App::instance()->user->allowed($permission);
    }

    public static function login($name, $password) {
        $db = App::instance()->db;
        $eh = App::instance()->eh;
        $db->where('username', $name);
        $db->orWhere('email', $name);
        $users = $db->get('users');
        if(count($users) > 0) {
            foreach($users as $user) {
                if(Utils::passwordCheck($password, $user['password'])) {
                    App::instance()->user = new User($user['id']);
                    $_SESSION['auth'] = $user['id'];
                    $eh->trigger('onLoginSuccess');
                } else {
                    $eh->trigger('onLoginFailed');
                }
            }
        } else {
            $eh->trigger('onLoginFailed');
        }
    }

    public static function registerPermissions($permissions) {
        if(!is_array($permissions)) {
            $permission = $permissions;
            // only one permission
            App::instance()->db->where('name', $permission);
            $dbPerm = App::instance()->db->get('permissions');
            if(count($dbPerm) == 0) {
                App::instance()->db->insert('permissions', array(
                    'name' => $permission
                ));
            }
            return;
        }
        $dbPerms = App::instance()->db->get('permissions');
        foreach($permissions as $toRegisterPerm) {
            $found = false;
            foreach($dbPerms as $dbPerm) {
                if($toRegisterPerm == $dbPerm['name']) {
                    $found = true;
                }
            }
            if(!$found) {
                App::instance()->db->insert('permissions', array(
                    'name' => $toRegisterPerm
                ));
            }
        }        
    }
}


?>