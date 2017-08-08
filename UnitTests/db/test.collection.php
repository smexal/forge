<?php

use PHPUnit\Framework\TestCase;
use \Forge\Core\App\App;

class TestOfCollection extends TestCase {

    private static $c_ids = [];

    public function testMetaSelect() {
        $collection = \Forge\Core\Tests\TestCollection::instance();
        static::$c_ids = UtilsTests::generateCollections(1, [
            'alpha_key_1' => 'alpha_value_1',
            'alpha_key_2' => 'alpha_value_2',
            'alpha_key_3' => 'alpha_value_3'
        ]);

        $this->assertEquals(1, count($collection->items([
            'meta_query' => [
                'alpha_key_1' => 'alpha_value_1'
            ]
        ])));

        static::$c_ids = array_merge(static::$c_ids, UtilsTests::generateCollections(3, [
            'beta_key_1' => 'beta_value_1',
            'beta_key_2' => 'beta_value_2',
            'beta_key_3' => 'beta_value_3'
        ]));

        $this->assertEquals(3, count($collection->items([
            'meta_query' => [
                'beta_key_1' => 'beta_value_1'
            ]
        ])));

        static::$c_ids = array_merge(static::$c_ids, UtilsTests::generateCollections(5, [
            'gamma_key_1' => 'gamma_key_1',
            'gamma_key_2' => 'gamma_key_2',
            'gamma_key_3' => 'gamma_key_3'
        ]));

        static::$c_ids = array_merge(static::$c_ids, UtilsTests::generateCollections(5, [
            'alpha_key_1' => 'alpha_value_1',
            'beta_key_1' => 'beta_value_1',
            'gamma_key_1' => 'gamma_key_1'
        ]));


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


        static::$c_ids = array_merge(static::$c_ids, UtilsTests::generateCollections(3, [
            'alpha_key_1' => 'alpha_value_1',
            'beta_key_1' => 'beta_value_1',
            'gamma_key_1' => 'gamma_key_1',
            'delta_key_1' => 'delta_key_1'
        ]));
        $this->assertEquals(3, count($collection->items([
            'meta_query' => [
                'alpha_key_1' => 'alpha_value_1',
                'beta_key_1' => 'beta_value_1',
                'gamma_key_1' => 'gamma_key_1',
                'delta_key_1' => 'delta_key_1'
            ]
        ])));

        UtilsTests::removeCollections(static::$c_ids);
        static::$c_ids = [];
    }

    public static function setUpBeforeClass() {
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .'class.utils.php');
        UtilsTests::prepare();
    }

    public static function tearDownAfterClass() {
        UtilsTests::teardown();
    }
}