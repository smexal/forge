<?php

namespace Forge\Core\App;

class CollectionManager {
  public $collections = null;

  public function __construct() {
    $this->getCollections();
  }

  public function add($args) {
    $db = App::instance()->db;
    $db->insert('collections', array(
      'sequence' => 0,
      'name' => $args['name'],
      'type' => $args['type'],
      'author' => App::instance()->user->get('id')
    ));
  }

  public function getCollection($name) {
    foreach($this->getCollections() as $col) {
      if($col->name == $name) {
        return $col;
      }
    }
  }

  public function getCollections() {
    if(is_array($this->collections)) {
      return $this->collections;
    }
    $classes = get_declared_classes();
    $implementsIModule = array();
    foreach($classes as $klass) {
      $reflect = new \ReflectionClass($klass);
      if($reflect->implementsInterface('Forge\Core\Interfaces\IDataCollection')) {
        if(! $reflect->isAbstract())
          $implementsIModule[] = $klass;
      }
    }
    $collections = array();
    foreach($implementsIModule as $collection) {
      $collections[] = $collection::instance();
    }
    $this->collections = $collections;
    return $this->collections;
  }

  public function deleteCollectionItem($id) {
    $db = App::instance()->db;
    $db->where('id', $id);
    $db->delete('collections');
  }
}


?>
