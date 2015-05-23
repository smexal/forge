<?php

class I18N {

  private static $instances;
  private static $available;
  private static $clang;
  private $lang;
  private $messages;

  private function __construct($lang) {
    $this->lang = $lang;
    $this->messages = array();
    $contents = @file_get_contents(DOC_ROOT.'/i18n/'.$this->lang.'.txt');
    $this->parseContents($contents);
  }

  private function parseContents($contents) {
    preg_match_all('/"([^"]+)"\s*:\s*"([^"]+)"/', $contents, $matches);
    if($matches)
      foreach($matches[1] as $key => $msgid) {
        $this->messages[preg_replace('/\s/', '', $msgid)] = $matches[2][$key];
        $this->messages[$msgid] = $matches[2][$key];
      }
  }

  public function instanceLang() {
    return $this->lang;
  }

  public static function instance($lang=false) {
    if(!$lang)
      $lang = I18N::lang();
    if(!$lang)
      return null;
    if(!isset(self::$instances))
      self::$instances = array();
    if(!array_key_exists($lang, self::$instances))
      self::$instances[$lang] = new I18N($lang);
    return self::$instances[$lang];
  }

  public static function lang() {
    if(I18N::$clang)
      return I18N::$clang;
    $avail = I18N::available();
    if(array_key_exists('lang', $_SESSION) && in_array($_SESSION['lang'], $avail)) {
      I18N::$clang = $_SESSION['lang'];
      return $_SESSION['lang'];
    }
    if(!($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])))
      return DEFAULT_LANGUAGE;
    if(preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list)) {
      $res = array_combine($list[1], $list[2]);
      $lang = false;
      $prio = 0;
      foreach($res as $n => $v) {
        $n = substr($n, 0, 2);
        $v = +$v ? +$v : 1;
        if((!$lang || $v > $prio) && in_array($n, $avail)) {
          $prio = $v;
          $lang = $n;
        }
      }
      if($lang) {
        $_SESSION['lang'] = $lang;
        I18N::$clang = $lang;
        return $lang;
      }
    }
    return DEFAULT_LANGUAGE;
  }

  public static function available() {
    if(!I18N::$available)
      I18N::$available = explode(',', AVAILABLE_LANGUAGES);
    return I18N::$available;
  }

  public static function name($lang) {
    return I18N::instance($lang)->i($lang);
  }

  public static function setLang($lang) {
    if(in_array($lang, I18N::available()))
      I18N::$clang = $lang;
  }

  public static function resetLang() {
    I18N::$clang = false;
  }

  public function i($msgid, $domain=false) {
    $msgid = array_key_exists(preg_replace('/\s/', '', $msgid), $this->messages) ? $this->messages[preg_replace('/\s/', '', $msgid)] : $msgid;
    $msgid = array_key_exists(preg_replace('/\n/', '\n', $msgid), $this->messages) ? $this->messages[preg_replace('/\n/', '\n', $msgid)] : $msgid;
    return $msgid;
  }

  public function messages() {
    return $this->messages;
  }

}

function i($msgid, $domain=false) {
  return I18N::instance()->i(trim($msgid));
}

?>