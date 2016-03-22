<?php
/** DEPRECATED IN CONCEPT... **/
class Sites extends DataCollection {
  public $permission = "manage.collection.sites";

  protected function setup() {
    $this->preferences['title'] = i('Sites');
    $this->preferences['all-title'] = i('Manage Sites');
    $this->preferences['add-label'] = i('Add site');
  }
}

?>
