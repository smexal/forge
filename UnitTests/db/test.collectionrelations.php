<?php

use PHPUnit\Framework\TestCase;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Relations\CollectionRelation as CollectionRelation;
use \Forge\Core\Classes\Relations\Enums\Directions as Directions;
use \Forge\Core\Classes\Relations\Enums\Prepares as Prepares;

class TestCollectionRelations extends TestCase {

    private static $c_ids = [];

    public function testGetInexistentRelation() {
        $returned_relation = App::instance()->rd->getRelation('I DO NOT EXIST');
        $this->assertEquals(null, $returned_relation, "Inexistent relation is null");
    }

    public function testAddCollectionRelations() {
        $collection = \Forge\Core\Tests\TestCollection::instance();
        $collectiontwo = \Forge\Core\Tests\TestCollectionTwo::instance();

        $relation = new CollectionRelation(
            'my-test-relation', 
            'testcollection', 
            'testcollectiontwo', 
            Directions::DIRECTED
        );

        App::instance()->rd->add('my-test-relation',  $relation);
        $returned_relation = App::instance()->rd->getRelation('my-test-relation');

        $this->assertEquals($relation, $returned_relation, "Is Equal");

    }

    public function testGetRelationItems() {
        $collection = \Forge\Core\Tests\TestCollection::instance();
        $collectiontwo = \Forge\Core\Tests\TestCollectionTwo::instance();


        $new_relation = new CollectionRelation(
            'my-test-relation2', 
            'testcollection', 
            'testcollectiontwo', 
            Directions::DIRECTED
        );

        App::instance()->rd->add('my-test-relation2', $new_relation);
        $relationUD = App::instance()->rd->getRelation('my-test-relation2');

        $empty = $relationUD->all();
        $this->assertEmpty($empty, "Is indeed empty");


        $c_ids_a = \UtilsTests::generateCollections(4, [], '\Forge\Core\Tests\TestCollection');
        $c_ids_b = \UtilsTests::generateCollections(4, [], '\Forge\Core\Tests\TestCollectionTwo');
        $links = [];
        foreach($c_ids_a as $key => $left) {
            $links[$left] = [];
            foreach($c_ids_b as $key2 => $right) {
                $links[$left][] = $right;
                $relationUD->add($left, $right);
            }
        }

        // VALIDATE GETTING AS ARRAY
        $right_ids = $relationUD->getOfLeft($c_ids_a[0], Prepares::AS_ITEM_RIGHT);
        $this->assertTrue(is_array($right_ids));
        $this->assertEquals(count($right_ids), count($c_ids_b));
        $this->assertTrue(is_numeric($right_ids[0]));

        // VALIDATE GETTING AS CollectioItem Instances
        $right_items = $relationUD->getOfLeft($c_ids_a[0], Prepares::AS_INSTANCE_RIGHT);
        $this->assertTrue(is_array($right_items));
        $this->assertEquals(count($right_items), count($c_ids_b));
        $this->assertTrue(is_object($right_items[0]));

        $this->assertTrue(in_array('Forge\\Core\\Interfaces\\ICollectionItem', class_implements($right_items[0])), "Implements the ICollectionItem, as expected");

        $left_keys = array_keys($links);
        $check_left_id = $left_keys[0];
        $check_right_ids = $left[$check_left_id];

        usort($right_items, function($a, $b) { 
            if($a->id === $b->id) return 0;
            return $a->id < $b->id ? -1 : 1;
        });
        asort($c_ids_b);

        for($i = 0; $i < count($right_items); $i++) {
            $generated_right_id = $c_ids_b[$i];
            $read_out_right_id   = $right_items[$i]->id;
            $this->assertEquals($generated_right_id, $read_out_right_id);
        }

    }

    public static function setUpBeforeClass() {
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .'class.utils.php');
        \UtilsTests::prepare();
    }

    public static function tearDownAfterClass() {
        \UtilsTests::teardown();
    }
}