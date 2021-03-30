<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\App\APIKeys;

class Localization {

    public static function getLanguages() {
        App::instance()->db->where('active', 1);
        App::instance()->db->orderBy('languages.default', "DESC");
        return App::instance()->db->get('languages');
    }

    // TODO: When time, allow to disable a language.. then this will become useful..
    public static function getActiveLanguages() {
        return self::getLanguages();
    }

    public static function languageIsActive($lang) {
        return in_array($lang, static::getActiveLanguages());
    }

    public static function getLanguageSelection() {
        $return = '<nav class="lang-sel"><ul>';
        foreach (Localization::getActiveLanguages() as $lang) {
            if ($lang['code'] != Localization::getCurrentLanguage()) {
                $return.='<li><a href="'.Utils::getUrl([$lang['code']]).'">'.$lang['name'].'</a></li>';
            }
        }
        $return.= '</ul></nav>';
        return $return;
    }

    public static function getLanguageInformation($code) {
        App::instance()->db->where('code', $code);
        return App::instance()->db->getOne('languages');
    }

    public static function setLang($lang_code) {
        $avail = self::getLanguages();
        foreach ($avail as $l) {
            if ($l['code'] == $lang_code) {
                $lang_found = true;
                break;
            }
        }
        if ($lang_found) {
            $_SESSION['lang'] = $lang_code;
            return $_SESSION['lang'];
        }
    }

    public static function getCurrentLanguage() {
        $avail = self::getLanguages();
        for ($index = 0; $index < count($avail); $index++) {
            $avail[$index] = $avail[$index]['code'];
        }
        if (array_key_exists('lang', $_GET) && in_array($_GET['lang'], $avail)) {
            $_SESSION['lang'] = $_GET['lang'];
            return $_SESSION['lang'];
        }
        if (array_key_exists('lang', $_SESSION) && in_array($_SESSION['lang'], $avail)) {
            return $_SESSION['lang'];
        }
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || !($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']))) {
            return DEFAULT_LANGUAGE;
        }
        if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list)) {
            $res = array_combine($list[1], $list[2]);
            $lang = false;
            $prio = 0;
            foreach ($res as $n => $v) {
                $n = substr($n, 0, 2);
                if(is_numeric($v)) {
                    $v = + $v ? +$v : 1;
                } else {
                    $v = 1;
                }
                if ((!$lang || $v > $prio) && in_array($n, $avail)) {
                    $prio = $v;
                    $lang = $n;
                }
            }
            if ($lang) {
                $_SESSION['lang'] = $lang;
                return $lang;
            }
        }
        return DEFAULT_LANGUAGE;
    }

    public static function stringTranslation($orignal, $domain='', $lang=false) {
        $db = App::instance()->db;
        $db->where("string", $db->escape($orignal));
        $db->where("domain", $db->escape($domain));
        $string = $db->getOne("language_strings");
        if (!$lang) {
            $lang = self::getCurrentLanguage();
        }
        $db->where("code", $lang);
        $lang = $db->getOne("languages");
        if ($string && $lang) {
            $db->where("stringid", $string['id']);
            $db->where("languageid", $lang['id']);
            $translation = $db->getOne("language_strings_translations");
            if ($translation) {
                return $translation['translation'];
            } else {
                return $orignal;
            }
        } else {
            return $orignal;
        }
    }

    public static function stringTranslationState($orignal, $domain, $lang=false) {
        $db = App::instance()->db;
        $db->where("string", $orignal);
        $db->where("domain", $domain);
        $string = $db->getOne("language_strings");
        $db->where("code", $lang);
        $lang = $db->getOne("languages");
        if ($string && $lang) {
            $db->where("stringid", $string['id']);
            $db->where("languageid", $lang['id']);
            $translation = $db->getOne("language_strings_translations");
            if ($translation) {
                return true;
            }
        }
        return false;
    }

    public static function addNewLanguage($code, $name) {
        $db = App::instance()->db;
        $db->where("code", $code);
        if ($db->getOne("languages") == 0) {
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
        if ($db->getOne("language_strings") == 0) {
            return false;
        } else {
            return true;
        }
    }

    public static function stringAmount() {
        $db = App::instance()->db;
        $count = $db->getValue("language_strings", "count(*)");
        return $count;
    }

    public static function addString($string, $domain='') {
        if (!Auth::allowed("manage.locales.strings.update")) {
            return;
        }

        if (! self::stringExists($string, $domain)) {
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
        if (count($db->getOne($table)) > 0) {
            $db->where("stringid", $stringid);
            $db->where("languageid", $lang);
            $db->update($table, $data);
        } else {
            $db->insert($table, array_merge(
                $data,
                ["stringid" => $stringid]
            ));
        }
    }

    public static function getStringById($id) {
        $db = App::instance()->db;
        $db->where('id', $id);
        return $db->getOne("language_strings");
    }

    public static function updateStrings($directory=DOC_ROOT, $recursive=true, $bar=false) {
        if (! Auth::allowed("manage.locales.strings.update")) {
            return;
        }

        $app = App::instance();
        $files = self::scanDirectory($directory, $recursive);

        if ($app->streamActive()) {
            echo Utils::screenLog(sprintf(i('Scanning %s *.php Files'), count($files)));
        }

        $current = 0;
        $strings = array();
        $newStrings = false;
        foreach ($files as $file) {
            $current++;
            if ($bar) {
                echo Utils::barUpdater($bar, 50/count($files)*$current);
            }
            $handle = fopen($file, "r");
            $linecount = 0;
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $linecount++;
                    $matches = preg_match_all('/i\\([\\"\\\'](.*?)(?<!\\\\)[\\"\\\'][, ]*[\\\'\\"]?(.*?)[\\"\\\']?\\)/', $line, $match_set, PREG_SET_ORDER);
                    // non php escaped regex: /i\([\"\'](.*?)(?<!\\)[\"\'][, ]*[\'\"]?(.*?)[\"\']?\)/
                    if ($matches > 0) {
                        foreach ($match_set as $match) {
                            array_push($strings, array(
                                "string" => $match[1],
                                "domain" => $match[2]
                            ));
                            if (! Localization::stringExists($match[1], $match[2])) {
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
        if (!$newStrings) {
            echo Utils::screenLog(i('No new strings found.'));
        }

        // check database for unused strings.
        $current = 0;
        $databaseStrings = self::getAllStrings();
        $amount = count($databaseStrings);
        echo Utils::screenLog(i("Checking for inactive Strings in the database..."));
        $action = false;
        $db = $app->db;
        foreach ($databaseStrings as $databaseString) {
            $current++;
            if ($bar) {
                echo Utils::barUpdater($bar, (50/$amount*$current)+50);
            }
            $found = false;
            foreach ($strings as $activeString) {
                if ($databaseString['string'] == $activeString['string'] && $databaseString['domain'] == $activeString['domain']) {
                    $found = true;
                }
            }
            // TODO: Somewhat this setting to used = 0 is not working. need to investigate *sherlock*
            $db->where("id", $databaseString['id']);
            if (! $found) {
                if ($databaseString['used'] == 1) {
                    $action = true;
                    $db->update("language_strings", array(
                        "used" => 0
                    ));
                    echo Utils::screenLog(sprintf(i('INACTIVE STRING: %s'),$databaseString['string']));
                }
            } else {
                if ($databaseString['used'] == 0) {
                    $action = true;
                    $db->update("language_strings", array(
                        "used" => 1
                    ));
                    echo Utils::screenLog(sprintf(i('ACTIVATE STRING: &gt;%s&lt;', "logs"),htmlentities($databaseString['string'])));
                }
            }
        }
        if (!$action) {
            echo Utils::screenLog(i('Nothing has changed, me friend..'));
        }
        echo Utils::screenLog(i('Translation String update complete.'));
    }

    public static function currentLang() {
        if (! array_key_exists('lang', $_GET)) {
            return Localization::getCurrentLanguage();
        } else {
            $codes = array();
            foreach (Localization::getActiveLanguages() as $lang) {
                $codes[] = $lang['code'];
            }
            if (in_array($_GET['lang'], $codes)) {
                return $_GET['lang'];
            } else {
                return Localization::getCurrentLanguage();
            }
        }
    }

    public static function getAllStrings($sort=false, $args = [], $page=false) {
        $db = App::instance()->db;

        if(array_key_exists('search', $args)) {
            $db->where('string', '%'.$args['search'].'%', 'LIKE');
            if(strlen($args['search']) > 0) {
                $page = false;
            }
        }

        if(array_key_exists('where', $args) && is_array($args['where'])) {
            foreach($args['where'] as $field => $value) {
                if($field == 'status') {
                    continue;
                }
                $db->where($field, $value);
            }
        }

        if ($sort && is_array($sort)) {
            $db->orderBy($sort[0], $sort[1]);
        }
        $db->orderBy("string", "asc");
        if($page == false) {
            $strings = $db->get("language_strings");
        } else {
            $db->pageLimit = PAGINATION_SIZE;
            $strings = $db->arraybuilder()->paginate('language_strings', $page);
        }
        if(array_key_exists('where', $args) && is_array($args['where']) && array_key_exists('status', $args['where'])) {
            // return only translated fields
            foreach($strings as $key => $string) {
                if($args['where']['status'] == 'translated') {
                    if( ! self::stringTranslationState($string['string'], $string['domain']) ) {
                        unset($strings[$key]);
                    }
                }
                if($args['where']['status'] == 'translation_missing') {
                    // return strings with missing translations
                    if( self::stringTranslationState($string['string'], $string['domain']) ) {
                        unset($strings[$key]);
                    }
                }
            }
        }
        return $strings;
    }

    public static function getTextDomains() {
        $results = App::instance()->db->get('language_strings', null, ['DISTINCT domain']);
        $domains = [];
        foreach($results as $result) {
            $domains[] = $result['domain'];
        }
        return $domains;
    }

    private static function scanDirectory($directory, $recursive) {
        $iterator = new \DirectoryIterator($directory);
        $files = array();
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            if ($fileInfo->isFile() && strstr($fileInfo->getFilename(), ".php")) {
                // check file
                array_push($files, $fileInfo->getRealPath());
            }
            if ($recursive && $fileInfo->isDir() && ! strstr($fileInfo->getFilename(), "cache")) {
                $files = array_merge(self::scanDirectory($fileInfo->getRealPath(), $recursive), $files);
            }
        }
        return $files;
    }

    public static function apiQuery($query, $format = 'json', $key=false) {
        if(($key && APIKeys::allowed("manage.locales", $key)) || Auth::allowed('manage.locales')) {
            if($format == 'xml') {
                return self::xmlApiQuery($query, $key);
            }
            if($format == 'json') {
                // TODO: make this json shizzle
            }
        }
    }

    public static function xmlApiQuery($query, $key) {
        $xml = new \DOMDocument('1.0', 'utf-8');
        $root = $xml->createElement("StringTranslations");
        foreach(self::getAllStrings(array('domain', 'asc')) as $string) {
            $xmlString = $xml->createElement("String");
            $xmlString->setAttribute('original', $string['string']);
            $xmlString->setAttribute('domain', $string['domain']);
            $xmlString->setAttribute('in-use', $string['used']);
            foreach(self::getActiveLanguages() as $lang) {
                $translation = self::stringTranslation($string['string'], $string['domain'], $lang['code']);
                
                $xmlTranslation = $xml->createElement('Translation');
                $xmlTranslation->setAttribute('lang', $lang['code']);
                $xmlTranslation->appendChild($xml->createTextNode($translation));
                $xmlString->appendChild($xmlTranslation);

            }
            $root->appendChild( $xmlString );
        }
        $xml->appendChild( $root );
        return $xml->saveXML();
    }
}