<?php

class Sites extends DataCollection {
  public $permission = "manage.collection.sites";

  protected function setup() {
    $this->preferences['title'] = i('Sites');
  }
}

?>
