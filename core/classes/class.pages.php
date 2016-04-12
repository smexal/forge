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
  private $customFields;

  public function __construct() {
    $this->app = App::instance();
    $this->db = $this->app->db;
    $this->customFields = array();
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

  public static function save($data) {
      $pages = new Pages();
      if(array_key_exists('pageid', $data)) {
          $page = new Page($data['pageid']);
          if(array_key_exists('lang', $data)) {
              App::instance()->addMessage(i('Language for saving values for page not set.'));
              return;
          }

          foreach($pages->fields() as $field) {
              if(! array_key_exists($field['key'], $data)) {
                  continue;
              }
              if($field['multilang'] == false) {
                  $lang = false;
              } else {
                  $lang = $data['language'];
              }
              self::savefield($page, $field['key'], $data[$field['key']], $lang);
          }
      } else {
          App::instance()->addMessage(i('Unable to save page, Page does not exist'));
      }
  }

  public static function savefield($page, $key, $value, $lang) {
      $page->updateMeta($key, $value, $lang);
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
   * This method deletes a page
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
        if(! array_key_exists('hint', $field)) {
            $field['hint'] = false;
        }
        array_push($this->customFields, $field);
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
                'label' => i('Title', 'core'),      // default value is "Label"
                'multilang' => true,
                'type' => 'text',                   // default value is text
                'order' => 2,                       // default value is 1000
                'position' => 'right',               // default is left
                'hint' => i('Will be used for title attribute (Search Engine and Social Media Title)')
            ),
            array(
                'key' => 'description',
                'label' => i('Description', 'core'),
                'multilang' => true,
                'type' => 'text',
                'order' => 3,
                'position' => 'right',
                'hint' => i('Will be used for description for Search Engines and Social Media')
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
