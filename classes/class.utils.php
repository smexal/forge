<?php

class Utils {
    public static function getUriComponents() {
        $uri = $_SERVER["REQUEST_URI"];
        $uri = str_replace(WWW_ROOT, "", $_SERVER["REQUEST_URI"]);
        return explode("/", $uri);
    }

    public static function password($raw) {
        return password_hash($raw, PASSWORD_BCRYPT);
    }
    public static function passwordCheck($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function getUrl($params = array()) {
        return WWW_ROOT.implode("/", $params);
    }
}


?>