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

  /*
   * This method deletes a pages
   * really. DELETES.
   */
  public function delete($id) {
    // delete a page. gone is gone.
    $app = App::instance();

    // update all pages which had this page as parent.
    // change it those root.
    $app->db->where('parent', $id);
    $app->db->update('pages', array(
      'parent' => 0
    ));

    $app->db->where('id', $id);
    if($app->db->delete('pages')) {
      return true;
    } else {
      return false;
    }

  }

}

 ?>
