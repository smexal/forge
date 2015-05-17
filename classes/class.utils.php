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

    public static function getCurrentUrl() {
        return self::getUrl(self::getUriComponents());
    }

    public static function getUrl($params = array()) {
        return WWW_ROOT.implode("/", $params);
    }

    public static function isAjax() {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return false;
    }
    
    public static function json($array) {
        return htmlspecialchars(json_encode($array), ENT_QUOTES, 'UTF-8');
    }
    
    public static function tableCell($content, $class=false, $id=false, $structure=false) {
      $data = array(
          'content' => $content,
          'class' => $class,
          'id' => $id
      );
      if($structure) {
        return App::instance()->render(TEMPLATE_DIR."/assets/", "table.cell", $data);
      }
      return $data;
    }
}


?>