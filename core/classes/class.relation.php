<?php

namespace Forge\Core\Classes;

use Forge\Core\App\App;
use Forge\Core\Classes\CollectionItem;

class Relation {
    const DIR_DIRECTED = 0x1;
    const DIR_BIDIRECT = 0x3;
    const DIRS = [0x1, 0x3];

    protected $identifier;
    protected $c_left;
    protected $c_right;
    protected $direction;

    public function __construct($identifier, $c_left, $c_right, $direction=Relation::DIR_DIRECTED) {
        if(true !== ($error = $this->validate($c_left, $c_right, $direction))) {
            throw new \Exception($error);
        }

        $this->identifier = $identifier;
        $this->c_left = $c_left;
        $this->c_right = $c_right;
        $this->direction = $direction;
    }

    public function validate($c_left, $c_right, $direction) {
        $db = App::instance()->db;
        $c_left = App::instance()->cm->getCollection($c_left);
        $c_right = App::instance()->cm->getCollection($c_right);
        
        if(!in_array($direction, Relation::DIRS)) {
            return "Invalid direction $dir ";
        }

        if(!$c_left) {
            return "Collection '$c_left' hasn't been found";
        }

        if(!$c_right) {
            return "Collection '$c_right' hasn't been found";
        }

        return true;

    }

    public function all() {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);

        $relations = $db->get('relations');
        return $relations;
    }

    public function getOfLeft($id_left) {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);
        $db->where('item_left', $id_left);
        
        $relations = $db->get('relations');
        return $relations;
    }

    public function getOfRight($id_right) {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);
        $db->where('item_right', $id_right);
        
        $relations = $db->get('relations');
        return $relations;
    }

    public function getOfBoth($id_left, $id_right) {
        $db = App::instance()->db;
        $db->orWhere('item_left', $id_left);
        $db->orWhere('item_right', $id_right);
        
        $relations = $db->get('relations');
        return $relations;
    }

    public function add($id_left, $id_right) {
        $db = App::instance()->db;
        $db->insert('relations', [
            'name' => $this->identifier,
            'item_left' => $id_left,
            'item_right' => $id_right
        ]);
        if($this->direction == Relation::DIR_BIDIRECT) {
            $db->insert('relations', [
                'name' => $this->identifier,
                'item_left' => $id_right,
                'item_right' => $id_left
            ]);
        }
    }

    public function purge() {
        $db = App::instance()->db;

        $db->join("collections c_left", "c_left.id = relations.item_left", 'RIGHT');
        $db->join("collections c_right", "c_right.id = relations.item_right", 'RIGHT');
        $db->where('relations.id', 'IS NULL');
       
        $relations = $db->get('relations');
        die(error_log(print_r($relations, 1)));
    }
}