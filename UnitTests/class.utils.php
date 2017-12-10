<?php
use Forge\Core\App\Auth;
use Forge\SuperLoader as SuperLoader;
use Forge\Core\App\App;

class UtilsTests {

    private static $app;
    private static $origin;

    public static function generateCollections($num, $metas=array(), $collection='\Forge\Core\Tests\TestCollection') {
        $db = App::instance()->db;
        $collection = $collection::instance();
        $ids = [];
        for($i = 0; $i < $num; $i++) {
            $ids[] = $c_id = $db->insert('collections', array(
              'sequence' => 0,
              'name' => 'TEST NAME' . $i,
              'type' => 'testcollection',
              'author' => 0
            ));

            $item = $collection->getItem($c_id);
            foreach($metas as $key => $value) {
                $item->updateMeta($key, $value, false);
            }
        }
        return $ids;
    }

    public static function prepare() {
        static $prepared;
        static::$origin = getcwd();
        if($prepared) {
            return;
        }
        $prepared = true;


        // Force correct path for including app
        $_SERVER['DOCUMENT_ROOT'] = realpath('..') . DIRECTORY_SEPARATOR;
        $_SERVER['SCRIPT_NAME'] = basename($_SERVER['DOCUMENT_ROOT']);
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-CH,de;q=0.8,de-DE;q=0.6,en-US;q=0.4,en;q=0.2,fr-CH;q=0.2,fr;q=0.2';

        chdir($_SERVER['DOCUMENT_ROOT']);
        require_once("config-tests.php");
        require_once("config.php");
        require_once("core/superloader.php");
        require_once("core/loader.php");

        // Don't do anything if the phpunit-env is not set correctly
        if(static::detectDanger()) {
            return;
        }

        // SuperLoader::$DEBUG = SuperLoader::DEBUG_PAGE;
        SuperLoader::$BASE_DIR = DOC_ROOT;
        SuperLoader::$FLUSH = AUTOLOADER_CLASS_FLUSH === true;
        spl_autoload_register(array(SuperLoader::instance(), "autoloadClass"));

        $loader = \Forge\Loader::instance();

        $mock_path = static::$origin . DIRECTORY_SEPARATOR . 'mocks' . DIRECTORY_SEPARATOR . 'collections' . DIRECTORY_SEPARATOR;

        require_once($mock_path . 'class.testdatacollection.php');
        require_once($mock_path . 'class.testdatacollectiontwo.php');

        @session_start();
        Auth::session();
        App::instance()->prepare();

        $db = \Forge\Core\App\App::instance()->db;
        static::doPurgeDB();

        App::instance()->cm->addCollection('\Forge\Core\Tests\TestCollection');
        App::instance()->cm->addCollection('\Forge\Core\Tests\TestCollectionTwo');

    }

    public static function teardown() {
        chdir(static::$origin);
        static::doPurgeDB();
    }

    public static function detectDanger() {
        if(!defined('ENV_PHP_UNIT_RUNNING') || ENV_PHP_UNIT_RUNNING !== true || DB_NAME !== 'butterlan_tests') {
            $msg = "WTF Dude!?? Why running the phpunit-tests with a different config?";
            print_r($msg);
            throw new \Exception($msg);
            return true;
        }
        return false;
    }

    public static function doPurgeDB() {
         // DANGER ZONE!
        if(static::detectDanger()) {
            return;
        }

        if(DB_NAME !== 'butterlan_tests') {
            throw new Error("WTF Dude? Why would you set a different DB than the test db?");
        }
  
        $db = App::instance()->db;
        $db->rawQuery('TRUNCATE TABLE `collections`');
        $db->rawQuery('TRUNCATE TABLE `collections_meta`');
        $db->rawQuery('TRUNCATE TABLE `relations`');

    }

}