<?php

namespace Forge\Core\App;

use Forge\Core\Abstracts\Manager;
use Forge\Core\App\Modifier;

use  Forge\Core\Classes\Relations\Enums\DefaultRelations;
use  Forge\Core\Classes\Relations\Relation;

class RelationDirectory {
  public $relations = [];

  public function __construct() {
  }

  public function start() {
    $this->relations = $this->collectRelations();
  }

  public function add($identifier, $relation) {
    $this->relations[$identifier] = $relation;
  }

  public function getRelation($identifier) {
    if(!isset($this->relations[$identifier])) {
      return null;
    }
    return $this->relations[$identifier];
  }

  public function collectRelations() {
      App::instance()->eh->fire("onGetRelations");
      $relations = \triggerModifier('Forge/Core/RelationDirectory/collectRelations', [
        DefaultRelations::PARENT_OF => new Relation(DefaultRelations::PARENT_OF)
      ]);
      App::instance()->eh->fire("onLoadedRelations", $relations);
      return $relations;
  }

}
