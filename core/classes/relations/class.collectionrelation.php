<?php

namespace Forge\Core\Classes\Relations;

use Forge\Core\App\App;
use Forge\Core\Classes\CollectionItem;
use Forge\Core\Classes\Relations\Relation as Relation;
use Forge\Core\Classes\Relations\Enums\Directions;
use Forge\Core\Classes\Relations\Enums\Prepares;

class CollectionRelation extends Relation implements \Forge\Core\Interfaces\IRelation {

    protected $c_left;
    protected $c_right;

    public function __construct($identifier, $c_left, $c_right, $direction=Directions::DIR_DIRECTED) {
        $this->c_left = App::instance()->cm->getCollection($c_left);
        $this->c_right = App::instance()->cm->getCollection($c_right);

        parent::__construct($identifier, $direction);
    }

    protected function validate() {
        if(true !== ($result = parent::validate())) {
            return $result;
        }

        if(!$this->c_left) {
            return "Collection '{$this->c_left}' hasn't been found";
        }

        if(!$this->c_right) {
            return "Collection '{$this->c_right}' hasn't been found";
        }

        return true;
    }

    protected function prepareRelations($relations, $prepare=Prepares::AS_ARRAY) {
        if(!in_array($prepare, [Prepares::AS_INSTANCE, Prepares::AS_INSTANCE_LEFT, Prepares::AS_INSTANCE_RIGHT])) {
            return parent::prepareRelations($relations, $prepare);
        }

        $list = [];
        foreach($relations as &$relation) {
            switch ($prepare) {
                case  Prepares::AS_INSTANCE:
                    $relation['item_left'] = $this->c_left->getItem($relation['item_left']);
                    $relation['item_right'] = $this->c_right->getItem($relation['item_right']);
                break;
                
                case Prepares::AS_INSTANCE_LEFT:
                    $list[] = $this->c_left->getItem($relation['item_left']);
                break;
                case Prepares::AS_INSTANCE_RIGHT:
                    $list[] = $this->c_left->getItem($relation['item_right']);
                break;
            }
        }

        if(count($list)) {
            return $list;
        }
        return $relations;
    }

/*
    public function purge() {
        $db = App::instance()->db;
        // TODO: WRITE TEST
        $db->join("collections AS c_left", "c_left.id = relations.item_left", 'RIGHT');
        $db->join("collections AS c_right", "c_right.id = relations.item_right", 'RIGHT');
        $db->orWhere('relations.item_left', 'IS NULL');
        $db->orWhere('relations.item_right', 'IS NULL');
       
        $relations = $db->get('relations');
        die(error_log(print_r($relations, 1)));
    }*/
}