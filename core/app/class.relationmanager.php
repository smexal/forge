<?php

namespace Forge\Core\App;
use \Forge\Core\Abstracts\Manager;
use \Forge\Core\App\Modifier;

class RelationManager extends Manager {
  public $relations = null;

  protected static $file_pattern = '/(.*)relation\.([a-zA-Z][a-zA-Z0-9]*)\.php$/';
  protected static $class_suffix = 'Relation';

  public function __construct() {
    parent::__construct();
    $this->getRelations();
  }

  public function add($args) {
    $db = App::instance()->db;
    return $db->insert('relations', array(
      'item_left' => $args['item_left'],
      'item_right' => $args['item_right'],
      'direction' => $args['direction']
    ));
  }

  public function getRelation($name) {
    foreach($this->getRelations() as $col) {
      if($col->name == $name) {
        return $col;
      }
    }
  }

  public function getRelations() {
    if(is_array($this->relations)) {
      return $this->relations;
    }

    $relation_classes = $this->_getRelations();
    $relations = array();
    foreach($relation_classes as $relation) {
      $relations[] = $relation::instance();
    }
    $this->relations = $relations;
    return $this->relations;
  }

  public function _getRelations() {
      App::instance()->eh->fire("onGetRelations");
      $flush_cache = \triggerModifier('Forge/RelationManager/FlushCache', MANAGER_CACHE_FLUSH === true);
      $classes = static::loadClasses($flush_cache);
      App::instance()->eh->fire("onLoadedRelations", $classes);
      return $classes;
  }

  public function deleteRelationItem($id) {
    $db = App::instance()->db;
    $db->where('id', $id);
    $db->delete('relations');
  }

    public function deleteRelationItem($left_id, $right_id) {
      $db = App::instance()->db;
      $db->where('left_id', $left_id);
      $db->where('right_id', $right_id);
      $db->delete('relations');
    }
}
