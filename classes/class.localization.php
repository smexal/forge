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

  public static function updateStrings($directory=DOC_ROOT, $recursive=true, $bar=false) {
    $app = App::instance();
    $files = self::scanDirectory($directory, $recursive);

    if($app->streamActive())
      echo Utils::screenLog(sprintf(i('Scanning %s *.php Files'), count($files)));

    $current = 0;
    foreach($files as $file) {
      $current++;
      if($bar) {
        echo Utils::barUpdater($bar, 100/count($files)*$current);
      }
      $handle = fopen($file, "r");
      $linecount = 0;
      if ($handle) {
        while (($line = fgets($handle)) !== false) {
          $linecount++;
          $matches = preg_match_all("/i\(['\"](.*?)['\"]\)/", $line, $match_set, PREG_PATTERN_ORDER);
          if($matches > 0) {
            if(!is_array($match_set[1])) {
              continue;
            }
            foreach($match_set[1] as $match) {
              echo Utils::screenLog(
                sprintf(
                  i('String: &lt;%1$s&gt; in file \'%2$s\' on line %3$s.'),
                  htmlentities($match),
                  basename($file),
                  $linecount
                )
              );
            }
          }
        }
        fclose($handle);
      } else {
        echo Utils::screenLog(sprintf(i('Could not read file: \'%s\''), basename($file)));
      }
    }
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
