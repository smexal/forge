<?php

use PHPUnit\Framework\TestCase;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Relation as Relation;

class TestRelations extends TestCase {

    private static $c_ids = [];

    public function testAddRelations() {
        $collection = \Forge\Core\Tests\TestCollection::instance();
        $collectiontwo = \Forge\Core\Tests\TestCollectionTwo::instance();

        $c_ids_a = \TestOfCollection::generateCollections(4, [], '\Forge\Core\Tests\TestCollection');
        $c_ids_b = \TestOfCollection::generateCollections(4, [], '\Forge\Core\Tests\TestCollectionTwo');

        $relationBD = new Relation('test-BIBIBIDIRECT', 'testcollection', 'testcollectiontwo', Relation::DIR_BIDIRECT);
        $relationUD = new Relation('test-____DIRECT', 'testcollection', 'testcollectiontwo', Relation::DIR_DIRECTED);

        static::$c_ids = array_merge($c_ids_a, $c_ids_b);

        $links = [];
        $links_reversed = [];

        foreach($c_ids_a as $key => $left) {
            $links[$left] = [];
            foreach($c_ids_b as $key2 => $right) {
                $links[$left][] = $right;
                $links_merged[$left][] = $right;
                $relationBD->add($left, $right);
                $relationUD->add($left, $right);
            }
        }

        foreach($c_ids_b as $key => $right) {
            $links_reversed[$right] = [];
            foreach($c_ids_a as $key2 => $left) {
                $links_reversed[$right][] = $left;
                $links_merged[$right][] = $left;
            }
        }

        asort($links);
        asort($links_reversed);
        asort($links_merged);

        foreach($links as $left_key => $set) {
            $test_bidir_left_ids = static::onlyRightIds($relationBD->getOfLeft($left_key));
            $this->assertEquals($test_bidir_left_ids, $links_merged[$left_key]);
        }
        
        foreach($links_reversed as $right_key => $set) {
            $test_bidir_right_ids  = static::onlyLeftIds($relationBD->getOfRight($right_key));
            $this->assertEquals($test_bidir_right_ids, $links_merged[$right_key]);
        }



        \TestOfCollection::removeCollections(static::$c_ids);
    }

    public static function onlyLeftIds($relations) {
        return array_map(function(&$elem) {
            return $elem['item_left'];
        }, $relations);
    }

    public static function onlyRightIds($relations) {
        return array_map(function(&$elem) {
            return $elem['item_right'];
        }, $relations);
    }

    public static function setUpBeforeClass() {
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .'class.utils.php');
        UtilsTests::prepare();
    }

    public static function tearDownAfterClass() {
        UtilsTests::teardown();
    }
}