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

  public static function getAll() {
    if(! Auth::allowed("manage.builder.pages")) {
      return array();
    }
    return App::instance()->db->get('pages', null, array("id", "name"));
  }

  public static function search($term) {
    if(! Auth::allowed("manage.builder.pages")) {
      return array();
    }
    App::instance()->db->where("name", $term."%", "LIKE");
    return App::instance()->db->get('pages', null, array("id", "name"));
  }

  public static function create($name, $parent) {
      if(! Auth::allowed("manage.builder.pages.add")) {
          return;
      }
      $nameStatus = self::checkName($name);
      if($nameStatus !== true) {
        return $nameStatus;
      }
      $app = App::instance();

      $data = array(
          'name' => $name,
          'parent' => $parent
      );
      $app->db->insert('pages', $data);
      return false;
  }

  private static function checkName($name) {
    $app = App::instance();
    if( strlen($name) <= 2 ) {
      return i('Pagename is too short.');
    }
    $app->db->where("name", $name);
    $app->db->get("pages");
    if($app->db->count > 0) {
      return i("A Page with that name already exists.");
    }
    return true;
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
