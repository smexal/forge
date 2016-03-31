<?php

class Pages {
  private $app;
  private $db;

  public function __construct() {
    $this->app = App::instance();
    $this->db = $this->app->db;
  }

  public function get($parent="0") {
    $this->db->orderBy("sequence", "asc");
    $this->db->where("parent", $parent);
    return $this->db->get('pages');
  }

}

 ?>
