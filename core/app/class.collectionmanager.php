<?php

namespace Forge\Core\App;

use Forge\Core\Abstracts\Manager;
use Forge\Core\App\Modifier;
use Forge\Core\Classes\CollectionItem;

class CollectionManager extends Manager {
  public $collections = null;

  protected static $file_pattern = '/(.*)collection\.([a-zA-Z][a-zA-Z0-9]*)\.php$/';
  protected static $class_suffix = 'Collection';

  public function __construct() {
    parent::__construct();
    $this->getCollections();
  }

  /**
   * @deprecated Collection Manager is not responsible for creating CollectionItems.
   */
  public function add($args) {
    \Forge\Core\Classes\Logger::debug("This method is deprecated. Please Use CollectionItem::create()");
    CollectionItem::create($args);
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

  public function addCollection($collection) {
    $col = $collection::instance();
    if($this->getCollection($col->name)) {
      throw new Exception("Collection is already registered");
    }
    $this->collections[] = $col;
  }

  public function _getCollections() {
      App::instance()->eh->fire("onGetCollections");
      $flush_cache = \triggerModifier('Forge/CollectionManager/FlushCache', MANAGER_CACHE_FLUSH === true);
      $classes = static::loadClasses($flush_cache);
      App::instance()->eh->fire("onLoadedCollections", $classes);
      return $classes;
  }

  /**
   * @deprecated
   */
  public function deleteCollectionItem($id) {
    Forge\Core\Classes\Logger::debug("This method is deprecated. Please Use CollectionItem->delete()");
    $item = new CollectionItem($id);
    $item->delete();
  }
}
