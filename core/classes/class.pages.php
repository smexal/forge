<?php
/**
* This class is made for the page handling in forge.
* Delivers the page objects, searches for pages and knowns what fields
* you want on the builder page. sweeet stuff :o
*
* @package    FORGE
* @author     SMEXAL
* @version    0.1
*/
class Pages {
  private $app;
  private $db;
  private $customFields = array();

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

    public function addField($field=array()) {
        if(! array_key_exists('key', $field)) {
            Logger::debug('<key> for field not set: '.implode(", ", $field));
            return;
        }
        if(! array_key_exists('multilang', $field)) {
            $field['multilang'] = true;
        }
        if(! array_key_exists('label', $field)) {
            $field['label'] = i('Label');
        }
        if(! array_key_exists('type', $field)) {
            $field['label'] = 'text';
        }
        if(! array_key_exists('order', $field)) {
            $field['order'] = 1000;
        }
        if(! array_key_exists('position', $field)) {
            $field['position'] = 'left';
        }
        array_push($this->$customFields, $fields);
    }

    public function addFields( $fields=array() ) {
        foreach($fields as $field) {
            if(is_array($field)) {
                $this->addField($field);
            }
        }
    }

    private function defaultFields() {
        $fields = array(
            array(
                'key' => 'title',
                'multilang' => true,                // default value is true
                'label' => i('Title', 'core'),      // default value is "Label"
                'type' => 'text',                   // default value is text
                'order' => 1,                       // default value is 1000
                'position' => 'left'                // default is left
            )
        );
        return $fields;
    }

    public function fields() {
        $fields = array_merge($this->defaultFields(), $this->customFields);
        return array_msort($fields, array('order'=>SORT_ASC, 'key'=>SORT_ASC));
    }

}

 ?>
