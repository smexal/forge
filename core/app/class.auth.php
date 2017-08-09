<?php

namespace Forge\Core\App;

use \Forge\Core\Classes\Logger;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Utils;

class Auth {
    public static function any() {
        return (isset($_SESSION['auth']) && is_numeric($_SESSION['auth']));
    }

    public static function getSessionUserID() {
        if(static::any()) 
            return $_SESSION['auth'];
        return null;
    }

    public static function setSessionUser() {
        if (Auth::any()) {
            App::instance()->user = new User($_SESSION['auth']);
        }
    }

    public static function destroy() {
        App::instance()->user = null;
        unset($_SESSION['auth']);
        session_destroy();
    }

    public static function allowed($permission, $inpage = false) {
        Logger::debug('Checking Permission: '. $permission);
        if (is_null($permission) || $permission == false) {
            // no permission required for this view.
            return true;
        }
        // not even logged in... send to login
        if (! Auth::any() || is_null(App::instance()->user)) {
            if ($inpage === false) {
                App::instance()->redirect(Utils::getUrl(array('login')), Utils::getCurrentUrl());
            } else {
                return false;
            }
        }
        return App::instance()->user->allowed($permission);
    }

    public static function login($name, $password, $trigger = false) {
        $db = App::instance()->db;
        $eh = App::instance()->eh;
        $db->where('username', $name);
        $db->orWhere('email', $name);
        $users = $db->get('users');
        if (count($users) > 0) {
            foreach ($users as $user) {
                if (Utils::passwordCheck($password, $user['password'])) {
                    App::instance()->user = new User($user['id']);
                    $_SESSION['auth'] = $user['id'];
                    if($trigger) {
                        $eh->trigger('onLoginSuccess');
                    }
                } else {
                    if($trigger) {
                        $eh->trigger('onLoginFailed');
                    }
                }
            }
        } else {
            if ($trigger) {
                $eh->trigger('onLoginFailed');
            }
        }
    }

    public static function registerPermissions($permissions) {
        if (!is_array($permissions)) {
            $permission = $permissions;
            // only one permission
            App::instance()->db->where('name', $permission);
            $dbPerm = App::instance()->db->get('permissions');
            if (count($dbPerm) == 0) {
                App::instance()->db->insert('permissions', array(
                    'name' => $permission
                ));
            }
            return;
        } else {
            $dbPerms = App::instance()->db->get('permissions');
            foreach ($permissions as $toRegisterPerm) {
                $found = false;
                foreach ($dbPerms as $dbPerm) {
                    if ($toRegisterPerm == $dbPerm['name']) {
                        $found = true;
                    }
                }
                if (!$found) {
                    App::instance()->db->insert('permissions', array(
                        'name' => $toRegisterPerm
                    ));
                }
            }
        }
    }

    public static function getPermissionID($permission_name) { 
        App::instance()->db->where('name', $permission_name);
        $dbPerm = App::instance()->db->get('permissions');
        if (count($dbPerm) == 0) {
            return null;
        }
        return $dbPerm[0]['id'];
    }

    public static function session() {
        // session has already been started
        if (session_status() !== PHP_SESSION_NONE)
            return;

        $session_name = 'forge';   // Set a custom session name
        $secure = SECURE;
        // This stops JavaScript being able to access the session id.
        $httponly = true;
        // Forces sessions to only use cookies.
        if (ini_set('session.use_only_cookies', 1) === FALSE) {
            exit();
        }
        // Gets current cookies params.
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams["lifetime"],
            $cookieParams["path"],
            $cookieParams["domain"],
            $secure,
            $httponly);
        // Sets the session name to the one set above.
        session_name($session_name);
        session_start(); // Start the PHP session
    }
}


