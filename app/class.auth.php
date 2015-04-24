<?php

class Auth {
    public static function any() {
        if(isset($_SESSION['auth']) && $_SESSION['auth'] === true) {
            return true;
        } else {
            return false;
        }
    }

    public static function destroy() {
        App::instance()->user = null;
        unset($_SESSION['auth']);
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
                    $_SESSION['auth'] = true;
                    $eh->trigger('onLoginSuccess');
                } else {
                    $eh->trigger('onLoginFailed');
                }
            }
        } else {
            $eh->trigger('onLoginFailed');
        }
    }
}


?>