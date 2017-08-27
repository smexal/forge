<?php

namespace Forge\Core\Classes\Relations;

use Forge\Core\App\App;
use Forge\Core\Classes\CollectionItem;
use Forge\Core\Classes\Relations\Enums\Directions;
use Forge\Core\Classes\Relations\Enums\Prepares;

class Relation implements \Forge\Core\Interfaces\IRelation {

    protected $identifier;
    protected $direction;

    protected $existing = [];

    public function __construct($identifier, $direction=Directions::DIRECTED) {
        $this->identifier = $identifier;
        $this->direction = $direction;
        if(true !== ($error = $this->validate())) {
            throw new \Exception($error);
        }
    }

    protected function validate() {
        if(!in_array($this->direction, Directions::DIRS)) {
            return "Invalid direction $this->direction";
        }

        return true;
    }

    // Override this to directly server instantiiated objects
    protected function prepareRelations($relations, $prepare=Prepares::AS_ARRAY) {
        $return = [];
        $fn = null;

        switch($prepare) {
            case Prepares::AS_ID:
                $fn = function() { return $rel['id']; };
            break;

            case Prepares::AS_LEFT_ITEM:
                $fn = function() { return $rel['item_left']; };
            break;

            case Prepares::AS_RIGHT_ITEM:
                $fn = function() { return $rel['item_right']; };
            break;
           
            case Prepares::AS_OBJECT:
                $fn = function() { return (object) $rel; };
            break;

            case Prepares::AS_ARRAY:
            default:
                $fn = null;
            break;
        }

        if(!is_callable($fn)) {
            return $relations;
        }

        return array_map($fn, $relations);
    }

    public function all($prepare=Prepares::AS_ARRAY) {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);

        $relations = $db->get('relations');
        return $this->prepareRelations($relations, $prepare);
    }

    public function getByRelationIds($id_left, $id_right, $prepare=Prepares::AS_ARRAY) {
        $db = App::instance()->db;
        $db->where('identifier', $this->identifier);
        $db->where('item_left', $id_left);
        $db->where('item_right', $id_right);

        $relations = $db->get('relations');

        if(count($relations)) {
            if(!$prepare) {
                return $relations[0];
            }
            return $this->prepareRelations($relations, $prepare)[0];
        }
        return null;
    }

    public function get($id, $prepare=Prepares::AS_ARRAY) { 
        $db = App::instance()->db;
        $db->where('id', $id);

        $relations = $db->get('relations');
        if(count($relations)) {
            if(!$prepare) {
                return $relations[0];
            }
            return $this->prepareRelations($relations, $prepare)[0];
        }
        return null;
    }

    public function getReverse($id, $prepare=Prepares::AS_ARRAY) {
        $relation = $this->get($id, false);
        if(is_null($relation)) {
            return null;
        }
        $rev_id = $this->getByRelationIds($relation['item_left'], $relation['item_right']);
        return $this->get($rev_id, $prepare);
    }

    public function getOfLeft($id_left, $prepare=Prepares::AS_ARRAY) {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);
        $db->where('item_left', $id_left);
        
        $relations = $db->get('relations');
        if(!$prepare) {
            return $relations;
        }
        return $this->prepareRelations($relations, $prepare);
    }

    public function getOfRight($id_right, $prepare=Prepares::AS_ARRAY) {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);
        $db->where('item_right', $id_right);
        
        $relations = $db->get('relations');
        if(!$prepare) {
            return $relations;
        }
        return $this->prepareRelations($relations, $prepare);
    }

    public function getOfBoth($id_left, $id_right, $prepare=Prepares::AS_ARRAY) {
        $db = App::instance()->db;
        $db->orWhere('item_left', $id_left);
        $db->orWhere('item_right', $id_right);
        
        $relations = $db->get('relations');
        if(!$prepare) {
            return $relations;
        }
        return $this->prepareRelations($relations, $prepare);
    }

    public function add($id_left, $id_right) {
        $db = App::instance()->db;
        $db->insert('relations', [
            'name' => $this->identifier,
            'item_left' => $id_left,
            'item_right' => $id_right
        ]);
        if($this->direction == Directions::DIR_BIDIRECT) {
            $db->insert('relations', [
                'name' => $this->identifier,
                'item_left' => $id_right,
                'item_right' => $id_left
            ]);
        }
    }
    
    public function addMultiple($id_left, $ids_right) {
        foreach($ids_right as $id_right) {
            $this->add($id_left, $id_right);
        }
    }

    public function remove($id, $bidirect=true) {
        $db = App::instance()->db;
        if($bidirect && $this->direction == Directions::DIR_BIDIRECT) {
            $other = $this->getReverse($id);
            if($other) {
                $this->remove($other['id'], false);
            }
        }
        $db->where('name', $this->identifier);
        $db->where('id', $id);
        $db->delete('relations');
    }

    public function removeByRelationItems($id_left, $ids_right) {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);
        $db->where('left_item', $id_left);
        $db->where('right_item', $ids_right, 'IN');

        $db->delete('relations');
    }

    public function removeAll($id_left) {
        $db = App::instance()->db;
        $db->where('name', $this->identifier);
        $db->where('left_item', $id_left);

        $db->delete('relations');
    }

    public function setRightItems($id_left, $ids_right) {
        $existing = $this->getOfLeft($id_left, Prepares::AS_RIGHT_ITEM);
        $add = array_diff($ids_right, $existing);
        $remove = array_diff($existing, $ids_right);
      /*   error_log("EXISTING");
        error_log(print_r($existing, 1));
        error_log("ADD");
        error_log(print_r($add, 1));
        error_log("REMOVE");
        error_log(print_r($remove, 1));*/
        
        if(count($remove) > 0) {
            $this->removeByRelationItems($id_left, $remove);
        }
        
        if(count($add) > 0) {
            $this->addMultiple($id_left, $add);
        }
    }
}