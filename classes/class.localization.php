<?php

class Localization {

  public static function getLanguages() {
    $db = App::instance()->db;
    return $db->get('languages');
  }

  public static function stringTranslation($orignal, $domain, $lang=false) {
    $db = App::instance()->db;
    $db->where("string", $orignal);
    $db->where("domain", $domain);
    $string = $db->getOne("language_strings");
    $db->where("code", $lang);
    $lang = $db->getOne("languages");
    if($string && $lang) {
      $db->where("stringid", $string['id']);
      $db->where("languageid", $lang['id']);
      $translation = $db->getOne("language_strings_translations");
      if($translation) {
        return $translation['translation'];
      } else {
        return $orignal;
      }
    } else {
      return $orignal;
    }
  }

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

  public static function stringExists($string, $domain='') {
    $db = App::instance()->db;
    $db->where("string", $string);
    $db->where("domain", $domain);
    if($db->getOne("language_strings") == 0) {
      return false;
    } else {
      return true;
    }
  }

  public static function addString($string, $domain='') {
    if(!Auth::allowed("manage.locales.strings.update")) {
      return;
    }

    if(! self::stringExists($string, $domain)) {
      $db = App::instance()->db;
      $db->insert("language_strings", array(
          "string" => $string,
          "domain" => $domain
      ));
    }
  }

  public static function translate($stringid, $lang, $translation) {
    $table = "language_strings_translations";
    $db = App::instance()->db;
    $data = array(
        "translation" => $translation,
        "languageid" => $lang 
    );
    $db->where("stringid", $stringid);
    $db->where("languageid", $lang);
    if(count($db->getOne($table)) > 0) {
      $db->where("stringid", $stringid);
      $db->where("languageid", $lang);
      $db->update($table, $data);
    } else {
      $db->insert($table, array_merge(
          $data,
          array("stringid" => $stringid
       )));
    }
  }

  public static function getStringById($id) {
    $db = App::instance()->db;
    $db->where('id', $id);
    return $db->getOne("language_strings");
  }

  public static function updateStrings($directory=DOC_ROOT, $recursive=true, $bar=false) {
    if(!Auth::allowed("manage.locales.strings.update")) {
      return;
    }

    $app = App::instance();
    $files = self::scanDirectory($directory, $recursive);

    if($app->streamActive()) {
      echo Utils::screenLog(sprintf(i('Scanning %s *.php Files'), count($files)));
    }

    $current = 0;
    $strings = array();
    $newStrings = false;
    foreach($files as $file) {
      $current++;
      if($bar) {
        echo Utils::barUpdater($bar, 50/count($files)*$current);
      }
      $handle = fopen($file, "r");
      $linecount = 0;
      if ($handle) {
        while (($line = fgets($handle)) !== false) {
          $linecount++;
          $matches = preg_match_all('/i\\([\\"\\\'](.*?)(?<!\\\\)[\\"\\\'][, ]*[\\\'\\"]?(.*?)[\\"\\\']?\\)/', $line, $match_set, PREG_SET_ORDER);
          // non php escaped regex: /i\([\"\'](.*?)(?<!\\)[\"\'][, ]*[\'\"]?(.*?)[\"\']?\)/
          if($matches > 0) {
            foreach($match_set as $match) {
              array_push($strings, array(
                  "string" => $match[1],
                  "domain" => $match[2]
              ));
              if(! Localization::stringExists($match[1], $match[2])) {
                $newStrings = true;
                Localization::addString($match[1], $match[2]);
                echo Utils::screenLog(
                    sprintf(
                        i('NEW STRING: &lt;%1$s&gt; - <small>FILE:\'%2$s\'</small> - <small>LINE:\'%3$s\'</small> - DOMAIN:\'%4$s\'', "logs"),
                        htmlentities($match[1]),
                        basename($file),
                        $linecount,
                        strlen($match[2]) > 0 ? htmlentities($match[2]) : i('Default')
                    )
                );
              }
            }
          }
        }
        fclose($handle);
      } else {
        echo Utils::screenLog(sprintf(i('Could not read file: \'%s\''), basename($file)));
      }
    }
    if(!$newStrings) {
      echo Utils::screenLog(i('No new strings found.'));
    }

    // check database for unused strings.
    $current = 0;
    $databaseStrings = self::getAllStrings();
    $amount = count($databaseStrings);
    echo Utils::screenLog("Checking for inactive Strings in the database...");
    $action = false;
    foreach($databaseStrings as $databaseString) {
      $current++;
      if($bar) {
        echo Utils::barUpdater($bar, (50/$amount*$current)+50);
      }
      $found = false;
      foreach($strings as $activeString) {
        if($databaseString['string'] == $activeString['string'] && $databaseString['domain'] == $activeString['domain']) {
          $found = true;
        }
      }
      $db = App::instance()->db;
      $db->where("id", $databaseString['id']);
      if(! $found) {
        if($databaseString['used'] == 1) {
          $action = true;
          $db->update("language_strings", array(
            "used" => 0
          ));
          echo Utils::screenLog(sprintf(i('INACTIVE STRING: %s'),$databaseString['string']));
        }
      } else {
        if($databaseString['used'] == 0) {
          $action = true;
          $db->update("language_strings", array(
            "used" => "1"
          ));
          echo Utils::screenLog(sprintf(i('ACTIVATE STRING: &gt;%s&lt;', "logs"),htmlentities($databaseString['string'])));
        }
      }
    }
    if(!$action) {
      echo Utils::screenLog(i('Nothing has changed, me friend..'));
    }
    echo Utils::screenLog(i('Translation String update complete.'));
  }

  public static function getAllStrings($sort=false) {
    $db = App::instance()->db;
    if($sort && is_array($sort)) {
      $db->orderBy($sort[0], $sort[1]);
    }
    $db->orderBy("string", "asc");
    return $db->get("language_strings");
  }

  private static function scanDirectory($directory, $recursive) {
    $iterator = new DirectoryIterator($directory);
    $files = array();
    foreach ($iterator as $fileInfo) {
      if($fileInfo->isDot()) {
        continue;
      }
      if($fileInfo->isFile() && strstr($fileInfo->getFilename(), ".php")) {
        // check file
        array_push($files, $fileInfo->getRealPath());
      }
      if($recursive && $fileInfo->isDir() && ! strstr($fileInfo->getFilename(), "cache")) {
        $files = array_merge(self::scanDirectory($fileInfo->getRealPath(), $recursive), $files);
      }
    }
    return $files;
  }

}

?>
