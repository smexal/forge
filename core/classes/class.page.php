<?

class Page {
  public $id, $parent, $sequence, $name, $modified, $created, $creator, $url, $status;
  private $db;

  public function __construct($id) {
    $this->db = App::instance()->db;
    $this->db->where('id', $id);
    $page = $this->db->getOne('pages');
    $this->id = $page['id'];
    $this->parent = $page['parent'];
    $this->sequence = $page['sequence'];
    $this->name = $page['name'];
    $this->modified = $page['modified'];
    $this->created = $page['created'];
    $this->url = $page['url'];
    $this->status = $page['status'];

    $this->db->where('page', $this->id);
    $this->meta = $this->db->get('page_meta');
  }

  public function getMeta($key, $lang = false) {
      if(!$lang) {
          $lang = Localization::getCurrentLanguage();
      }
      foreach($this->meta as $meta) {
          if($meta['keyy'] == $key && $meta['lang'] == $lang) {
              return $meta['value'];
          }
      }
      return false;
  }

  /**
   * This is the shit
   *
   * @return User Object
   */
  public function author() {
    return new User($this->creator);
  }

  public function setMeta($key, $value, $language) {
      $this->db->where('keyy', $key);
      $this->db->where('page', $this->id);
      $this->db->where('lang', $language);
      $this->db->update('page_meta', array(
          'value' => $value
      ));
  }

  public function insertMeta($key, $value, $language) {
      if(strlen($value) == 0) {
          return;
          // don't save if we don't have anything to save...
      }
      $this->db->insert('page_meta', array(
          'keyy' => $key,
          'lang' => $language,
          'page' => $this->id,
          'value' => $value
      ));
  }

  public function deleteMeta($key, $language) {
      $this->db->where('keyy', $key);
      $this->db->where('lang', $language);
      $this->db->delete('page_meta');
  }

  public function updateMeta($key, $value, $language) {
      $current_value = $this->getMeta($key, $language);
      if(strlen($value) == 0) {
          // remove meta value, if there is no value
          $this->deleteMeta($key, $language);
      }
      if($current_value) {
          // update with new
          $this->setMeta($key, $value, $language);
      } else {
          // insert new value
          $this->insertMeta($key, $value, $language);
      }
  }

  public function addElement($type, $language, $parent=0, $position="end", $position_x = 0) {
      $data = array(
          'pageid' => $this->id,
          'elementid' => $type,
          'prefs' => '',
          'parent' => $parent,
          'lang' => $language,
          'position' => $position == 'end' ? $this->getNextElementPosition($parent, $language, $position_x) : $position,
          'position_x' => $position_x
      );
      $this->db->insert('page_elements', $data);
  }

  public function getElements($parent, $lang) {
      $this->db->where('parent', $parent);
      $this->db->where('lang', $lang);
      $this->db->where('pageid', $this->id);
      $elements = array();
      foreach($this->db->get('page_elements') as $element) {
          $element = App::instance()->com->instance($element['id'], $element['elementid']);
          if(!is_null($element)) {
              array_push($elements, $element);
          }
      }
      return $elements;
  }

  private function getNextElementPosition($parent, $language, $position_x = 0) {
      $this->db->where('parent', $parent);
      $this->db->where('pageid', $this->id);
      $this->db->where('position_x', $position_x);
      $this->db->where('lang', $language);
      $this->db->get('page_elements');
      return $this->db->count;
  }

  public function render() {
      return $this->getMeta('title');
  }

}

?>
