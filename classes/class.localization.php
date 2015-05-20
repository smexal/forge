<?php

class Localization {

  public static function addNewLanguage($code, $name) {
    $db = App::instance()->db;
    $db->where("code", $code);
    if( $db->getOne("languages") == 0 ) {
      $db->insert("languages", array(
          "code" => $code,
          "name" => $name
      ));
      return true;
    } else {
      return i('A language with that code already exists.');
    }
  }

  public static function setDefault($id) {
    $db = App::instance()->db;
    $db->update("languages", array(
      "default" => 0
    ));
    $db->where("id", $id);
    $db->update("languages", array(
      "default" => 1
    ));
  }

}

?>
