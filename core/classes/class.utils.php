<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

class Utils {
    public static function getUriComponents() {
        preg_match_all("/(.*)(\?.+)/", $_SERVER["REQUEST_URI"], $uri, PREG_PATTERN_ORDER);
        if(count($uri[0]) > 0) {
          $uri = $uri[1][0];
        } else {
          $uri = $_SERVER['REQUEST_URI'];
        }
        if(WWW_ROOT != '/') {
          $uri = str_replace(WWW_ROOT, "",$uri);
        }
        $uri = explode("/", $uri);
        foreach($uri as $k => $v) {
          if($v == '') {
            unset($uri[$k]);
          }
        }
        return array_values($uri);
    }

    public static function getServerRoot() {
      $root = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
      $dir = str_replace("\\", "/", dirname($_SERVER['SCRIPT_NAME']));
      $ext = str_replace($root, '', $dir);
      if(substr($ext, strlen($ext)-1) != '/') {
        $ext.="/";
      }
      return $ext;
    }

    public static function getHomeUrl() {
      return self::getAbsoluteUrlRoot().self::getServerRoot();
    }

    public static function getAbsoluteUrlRoot() {
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $domainName = $_SERVER['HTTP_HOST'];
      return $protocol.$domainName;
    }

    public static function password($raw) {
        /** ATTENTION WHEN CHANGING; THIS IS ALSO USED IN THE INSTALLER */
        return password_hash($raw, PASSWORD_BCRYPT);
    }
    public static function passwordCheck($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function getCurrentUrl() {
        return self::getUrl(self::getUriComponents());
    }

    public static function isJSON($string) {
      @json_decode($string);
      return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function maybeJSON($value='') {
        if (Utils::isJSON($value)) {
            $value = json_decode($value);
        }
        return $value;
    }

    public static function getUsername($id) {
      $user = new User($id);
      return $user->get('username');
    }

    public static function iconAction($icon, $type, $url) {
        switch($type) {
            case 'noOverlay':
                return '<a class="ajax" href="'.$url.'"><i class="material-icons">'.$icon.'</i></a>';
            default:
                return '<a class="ajax confirm" href="'.$url.'"><i class="material-icons">'.$icon.'</i></a>';
        }
    }

    public static function url($params = array(), $addGET=false, $additionalGET = array(), $language = false) {
        return self::getUrl($params, $addGET, $additionalGET, $language);
    }

    public static function getUrl($params = array(), $addGET=false, $additionalGET = array(), $language = false) {
        $query = '';
        if($addGET) {
            if(is_array($additionalGET)) {
                $get = array_merge($_GET, $additionalGET);
            } else {
                $get = $_GET;
            }
            $query = "?".http_build_query($get);
            // remove ? if no get parameters are set.
            if(strlen($query) == 1)
                $query = '';
        }
        $start = WWW_ROOT;
        if($language) {
          $start.= Localization::getCurrentLanguage()."/";
        }
        return $start.implode("/", $params).$query;
    }

    public static function getLanguageUrl($params = array(), $addGET=false, $additionalGET = array()) {
      return self::getUrl($params, $addGET, $additionalGET, true);
    }

    public static function getProgressBar($id, $current, $text="") {
      return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "progressbar", array(
          "id" => $id,
          "current" => $current,
          "min" => "0",
          "max" => "100",
          "text" => $text
      ));
    }

    public static function barUpdater($id, $value) {
      return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "barupdater", array(
          "id" => $id,
          "value" => $value
      ));
    }

    public static function screenLog($message) {
      return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "screenlog", array(
          "time" => date("H:i:s"),
          "message" => $message
      ));
    }

    public static function dateFormat($date, $long = false) {
      $time = strtotime($date);
      if($long) {
        return date('d.m.Y H:i',$time);
      }
      return date('d.m.Y',$time);
    }

    public static function isAjax() {
        if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        || array_key_exists("forceAjax", $_GET)) {
            return true;
        }
        return false;
    }

    public static function json($array) {
        return htmlspecialchars(json_encode($array), ENT_QUOTES, 'UTF-8');
    }

    public static function buffer() {
      if(array_key_exists("buffer", $_GET)) {
        if($_GET['buffer'] == 'false') {
          return false;
        }
      }
      return true;
    }

    public static function octetStream() {
      header('Content-type: application/octet-stream');

      // Turn off output buffering
      ini_set('output_buffering', 'off');
      // Turn off PHP output compression
      ini_set('zlib.output_compression', false);
      // Implicitly flush the buffer(s)
      ini_set('implicit_flush', true);
      ob_implicit_flush(true);
      // Clear, and turn off output buffering
      while (ob_get_level() > 0) {
          // Get the curent level
          $level = ob_get_level();
          // End the buffering
          ob_end_clean();
          // If the current level has not changed, abort
          if (ob_get_level() == $level) break;
      }
      // Disable apache output buffering/compression
      if (function_exists('apache_setenv')) {
          apache_setenv('no-gzip', '1');
          apache_setenv('dont-vary', '1');
      }
    }

    /**
     * http://stackoverflow.com/questions/2955251/php-function-to-make-slug-url-string
     **/
    static public function slugify($text) {
      // replace non letter or digits by -
      $text = preg_replace('~[^\pL\d]+~u', '-', $text);

      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);

      // trim
      $text = trim($text, '-');

      // remove duplicate -
      $text = preg_replace('~-+~', '-', $text);

      // lowercase
      $text = strtolower($text);

      if (empty($text)) {
        return 'n-a';
      }

      return $text;
    }

    public static function isEmail($email) {
      return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function overlayButton($url, $label, $target='', $classes = '') {
        $t = $target !== '' ? 'data-target="'.$target.'"' : '';
        $overlay = $t == '' ? 'open-overlay' : 'close-overlay';
      return '<a href="javascript://" data-open="'.$url.'" '.$t.' class="btn btn-primary '.$overlay.' '.$classes.'">'.$label.'</a>';
    }

    public static function tableCell($content, $class=false, $id=false, $structure=false, $action=false) {
      $data = array(
          'content' => $content,
          'class' => $class,
          'id' => $id,
          'rowAction' => $action
      );
      if($structure) {
        return App::instance()->render(CORE_TEMPLATE_DIR."/assets/", "table.cell", $data);
      }
      return $data;
    }

    public static function icon($name) {
      return '<i class="material-icons">'.$name.'</i>';
    }

    public static function error($error) {
      return '<div class="bs-callout bs-callout-danger"><p>'.$error.'</p></div>';
    }

    public static function formatAmount($amount) {
      // TODO: Make Currency from a Setting....
      $currency = 'CHF';
      return sprintf(i('%1$s %2$s', 'core-currency'), $currency, number_format($amount, 2, '.', '\''));
    }

    public static function extractParams($defaults, $params) {
      $values = [];
      
      $ctr = -1;
      foreach($defaults as $key => $default) {
          $ctr++;
          $values[$key] = isset($params[$ctr]) ? $params[$ctr] : $defaults[$key];
      }
      
      $more = [];
      while($ctr < count($params) -1) {
          $more[] = $params;
      } 
      $values['_more_'] = $more;

      return $values;
    }

    public static function methodName($string) {
      $string = ucwords($string, "-");
      return str_replace("-", "", $string);
    }

    /**
     * DEVELOPMENT UTILS
     */
    public static function stacktrace($max=5, $cut_left=0, $dbt=false, $output=false, $include_args=false) {
        $dbt_false = $dbt;
        $dbt = !$dbt ? debug_backtrace() : $dbt;

        $echo = 'Stack-size: ' . count($dbt) . "<br>\n";
        $i=0;
        foreach($dbt as $frame) {
            $i++;
            if(!array_key_exists('line', $frame)) {
                $args = array_map(function($e) {
                    return substr(print_r($e, 1), 0, 100) . "##\n";
                }, $frame['args']);

                $more = $include_args ? '' : '(' . implode(',' , $args) . ')';
                $echo .= htmlspecialchars($frame['function'] . $more). "<br>\n";
            } else  {
                $echo .= substr($frame['file'], $cut_left) . '::'. $frame['line'] . "<br>\n";
            }
            if($i == $max) {
                $echo .= "----- CUTTING REST (" . (count($dbt) - $i). ") -----\n";
                break;
            }
        }

        if($output)
          echo $echo;
        else
          return $echo;
    }


}
