<?php



use PHPUnit\Framework\TestCase;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\SuperLoader as SuperLoader;



class TestCollection extends TestCase {
    
    private static $app;
    private static $origin;
    private static $c_ids = [];

    public function testMetaSelect() {
        $collection = \Forge\Core\Tests\TestCollection::instance();
        static::$c_ids = static::generateCollections(1, [
            'alpha_key_1' => 'alpha_value_1',
            'alpha_key_2' => 'alpha_value_2',
            'alpha_key_3' => 'alpha_value_3'
        ]);

        $this->assertEquals(1, count($collection->items([
            'meta_query' => [
                'alpha_key_1' => 'alpha_value_1'
            ]
        ])));

        static::$c_ids = static::$c_ids + static::generateCollections(3, [
            'beta_key_1' => 'beta_value_1',
            'beta_key_2' => 'beta_value_2',
            'beta_key_3' => 'beta_value_3'
        ]);

        $this->assertEquals(3, count($collection->items([
            'meta_query' => [
                'beta_key_1' => 'beta_value_1'
            ]
        ])));

        static::$c_ids = static::$c_ids + static::generateCollections(5, [
            'gamma_key_1' => 'gamma_key_1',
            'gamma_key_2' => 'gamma_key_2',
            'gamma_key_3' => 'gamma_key_3'
        ]);

        static::$c_ids = static::$c_ids + static::generateCollections(5, [
            'alpha_key_1' => 'alpha_value_1',
            'beta_key_1' => 'beta_value_1',
            'gamma_key_1' => 'gamma_key_1'
        ]);


        $this->assertEquals(8, count($collection->items([
            'meta_query' => [
                'beta_key_1' => 'beta_value_1'
            ]
        ])));

        $this->assertEquals(0, count($collection->items([
            'meta_query' => [
                'beta_key_1' => 'INEXISTENT VALUE'
            ]
        ])));
        $this->assertEquals(0, count($collection->items([
            'meta_query' => [
                'INEXISTENT_KEY' => 'asdf'
            ]
        ])));
        $this->assertEquals(5, count($collection->items([
            'meta_query' => [
                'alpha_key_1' => 'alpha_value_1',
                'beta_key_1' => 'beta_value_1'
            ]
        ])));
        static::removeCollections(static::$c_ids);
        static::$c_ids = [];
    }


    public static function removeCollections($ids) {
        $db = App::instance()->db;
        foreach($ids as $c_id) {
            $db->query('DELETE FROM `collections` WHERE `collections`.`id` = ' . $c_id);
            $db->query('DELETE FROM `collection_meta` WHERE `collection_meta`.`item` = ' . $c_id);
        }
    }

    public static function generateCollections($num, $metas=array()) {
        $db = App::instance()->db;
        $collection = \Forge\Core\Tests\TestCollection::instance();
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

    public static function setUpBeforeClass() {
        static::$origin = getcwd();
        
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


        @session_start();
        Auth::session();
        App::instance()->prepare();
    }

    public static function tearDownAfterClass() {
        static::removeCollections(static::$c_ids);
        chdir(static::$origin);
    }
}