<?php

namespace Forge\Core\App;
use \Forge\Core\Abstracts\Manager;
use \Forge\Core\App\Modifier;

class CollectionManager extends Manager {
  public $collections = null;

  protected static $file_pattern = '/(.*)collection\.([a-zA-Z][a-zA-Z0-9]*)\.php$/';
  protected static $class_suffix = 'Collection';

  public function __construct() {
    parent::__construct();
    $this->getCollections();
  }

  public function add($args) {
    $db = App::instance()->db;
    return $db->insert('collections', array(
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

    $collection_classes = $this->_getCollections();
    $collections = array();
    foreach($collection_classes as $collection) {
      $collections[] = $collection::instance();
    }
    $this->collections = $collections;
    return $this->collections;
  }

  public function _getCollections() {
      App::instance()->eh->fire("onGetCollections");
      $flush_cache = \triggerModifier('Forge\CollectionManager\FlushCache', MANAGER_CACHE_FLUSH === true);
      $classes = static::loadClasses($flush_cache);
      App::instance()->eh->fire("onLoadedCollections", $classes);
      return $classes;
  }

  public function deleteCollectionItem($id) {
    $db = App::instance()->db;
    $db->where('id', $id);
    $db->delete('collections');
  }
}


