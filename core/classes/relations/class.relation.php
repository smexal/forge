<?php

namespace Forge\Core\Classes\Relations;

use Forge\Core\App\App;
use Forge\Core\Classes\CollectionItem;

class Relation implements \Forge\Core\Interfaces\IRelation {

    const DIR_DIRECTED = 0x1;
    const DIR_REVERSED = 0x2;
    const DIR_BIDIRECT = 0x3;

    // Reversed should not be used here as it is basically just a DIR_DIRECTED 
    const DIRS = [0x1, 0x3];

    protected $identifier;
    protected $direction;

    protected $existing = [];

    public function __construct($identifier, $direction=Relation::DIR_DIRECTED) {
        $this->identifier = $identifier;
        $this->direction = $direction;
        if(true !== ($error = $this->validate())) {
            throw new \Exception($error);
        }

    }

    protected function validate() {
        if(!in_array($this->direction, Relation::DIRS)) {
            return "Invalid direction $this->direction";
        }

        return true;
    }

    // Override this to directly server instantiiated objects
    protected function prepareRelations($relations) {
        return $relations;
    }

    public function all($prepared=true) {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);

        $relations = $db->get('relations');
        return $this->prepareRelations($relations);
    }

    public function getByRelationIds($id_left, $id_right, $prepared=true) {
        $db = App::instance()->db;
        $db->where('identifier', $this->identifier);
        $db->where('item_left', $id_left);
        $db->where('item_right', $id_right);

        $relations = $db->get('relations');

        if(count($relations)) {
            if(!$prepared) {
                return $relations[0];
            }
            return $this->prepareRelations($relations)[0];
        }
        return null;
    }

    public function get($id, $prepared=true) { 
        $db = App::instance()->db;
        $db->where('id', $id);

        $relations = $db->get('relations');
        if(count($relations)) {
            if(!$prepared) {
                return $relations[0];
            }
            return $this->prepareRelations($relations)[0];
        }
        return null;
    }

    public function getReverse($id, $prepared=true) {
        $relation = $this->get($id, false);
        if(is_null($relation)) {
            return null;
        }
        $rev_id = $this->getId($relation['item_left'], $relation['item_right']);
        return $this->get($rev_id, $prepared);
    }

    public function getOfLeft($id_left, $prepared=true) {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);
        $db->where('item_left', $id_left);
        
        $relations = $db->get('relations');
        if(!$prepared) {
            return $relations;
        }
        return $this->prepareRelations($relations);
    }

    public function getOfRight($id_right, $prepared=true) {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);
        $db->where('item_right', $id_right);
        
        $relations = $db->get('relations');
        if(!$prepared) {
            return $relations;
        }
        return $this->prepareRelations($relations);
    }

    public function getOfBoth($id_left, $id_right, $prepared=true) {
        $db = App::instance()->db;
        $db->orWhere('item_left', $id_left);
        $db->orWhere('item_right', $id_right);
        
        $relations = $db->get('relations');
        if(!$prepared) {
            return $relations;
        }
        return $this->prepareRelations($relations);
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

    public function remove($id, $bidirect=true) {
        $db = App::instance()->db;
        if($bidirect && $this->direction == Relation::DIR_BIDIRECT) {
            $other = $this->getReverse($id);
            if($other) {
                $this->remove($other['id'], false);
            }
        }
        $db->where('name', $this->identifier);
        $db->where('id', $id);
        $db->delete('relations');
    }
}