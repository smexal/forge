<?php
use \Forge\Core\App\Auth;
use \Forge\SuperLoader as SuperLoader;
use \Forge\Core\App\App;

class UtilsTests {

    private static $app;
    private static $origin;

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
        $_SERVER['SCRIPT_NAME'] = basename($_SERVER['DOCUMENT_ROOT']);
        $_SERVER['SCRIPT_NAME'] = basename($_SERVER['DOCUMENT_ROOT']);
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-CH,de;q=0.8,de-DE;q=0.6,en-US;q=0.4,en;q=0.2,fr-CH;q=0.2,fr;q=0.2';

        chdir($_SERVER['DOCUMENT_ROOT']);
        include("config.php");
        include("core/superloader.php");
        include("core/loader.php");

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

        App::instance()->cm->addCollection('\Forge\Core\Tests\TestCollection');
        App::instance()->cm->addCollection('\Forge\Core\Tests\TestCollectionTwo');

    }

    public static function teardown() {
        chdir(static::$origin);
    }

}