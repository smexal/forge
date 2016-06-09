<?php

abstract class DataCollection implements IDataCollection {
  public $permission = null;
  protected static $instances = array();
  protected $app;
  public $preferences = array();
  public $name = false;
  private $customFields = array();

  abstract protected function setup();

  public function getPref($name) {
    return $this->preferences[$name];
  }

  public static function savefield($item, $key, $value, $lang) {
      $item->updateMeta($key, $value, $lang);
  }

  private function init() {
    $this->app = App::instance();
    $this->setup();
    if(!is_null($this->permission)) {
      Auth::registerPermissions($this->permission);
    }
    $this->preferences = array(
      'name' => strtolower(get_class($this)),
      'title' => i('Data'),
      'all-title' => i('All Collection Items'),
      'add-label' => i('Add item'),
      'single-item' => i('item')
    );
    $this->setup();
    $this->name = $this->getPref('name');
  }

  public function items() {
    $db = App::instance()->db;
    $db->where('type', $this->name);
    return $db->get('collections');
  }

  public function getItem($id) {
    return new CollectionItem($id);
  }

  public function save($data) {
      if(array_key_exists('itemid', $data)) {
          $item = new CollectionItem($data['itemid']);
          if(array_key_exists('lang', $data)) {
              App::instance()->addMessage(i('Language for saving values for page not set.'));
              return;
          }

          foreach($this->fields() as $field) {
              if(! array_key_exists($field['key'], $data)) {
                  continue;
              }
              if($field['multilang'] == false) {
                  $lang = false;
              } else {
                  $lang = $data['language'];
              }
              self::savefield($item, $field['key'], $data[$field['key']], $lang);
          }
      } else {
          App::instance()->addMessage(i('Unable to save item, Item does not exist'));
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
                'position' => 'left',               // default is left
                'hint' => i('Will be used for title attribute (Search Engine and Social Media Title)')
            ),
            array(
                'key' => 'description',
                'label' => i('Description', 'core'),
                'multilang' => true,
                'type' => 'text',
                'order' => 3,
                'position' => 'left',
                'hint' => i('Will be used for description for Search Engines and Social Media')
            ),
            array(
                'key' => 'status',
                'label' => sprintf(i('%s status'), $this->preferences['single-item']),
                'multilang' => true,
                'type' => 'select',
                'values' => array(
                    'draft' => i('Draft'),
                    'published' => i('Published')
                ),
                'order' => 1,
                'position' => 'right',
                'hint' => ''
            ),
            array(
                'key' => 'slug',
                'label' => i('URL Part'),
                'multilang' => true,
                'type' => 'text',
                'order' => 10,
                'position' => 'right',
                'hint' => sprintf(
                    i('This field will be used to find the %1$s with an url. If not set, the name of the %1$s will be used.'),
                    $this->preferences['single-item'])
            )
        );
        return $fields;
    }

    public function addFields( $fields=array() ) {
        foreach($fields as $field) {
            if(is_array($field)) {
                $this->addField($field);
            }
        }
    }

    public function getCategories($parent = 0) {
      $db = App::instance()->db;
      $db->where('collection', $this->name);
      $db->where('parent', $parent);
      $cats = $db->get('collection_categories');
      return $cats;
    }

    public function getCategoryMeta($id, $lang=false) {
      if(!$lang) {
        $lang = Localization::getCurrentLanguage();
      }
      $db = App::instance()->db;
      $db->where('id', $id);
      $cat = $db->getOne('collection_categories');
      $json = json_decode($cat['meta']);
      return $json->$lang;
    }

    public function addCategory($data) {
      if(!array_key_exists("name", $data)) {
        $data['name'] = "(no name)";
      }
      if(!array_key_exists("parent", $data)) {
        $data['parent'] = 0;
      }
      $db = App::instance()->db;
      $category = $db->insert("collection_categories", array(
        "collection" => $this->name,
        "meta" => '',
        "parent" => $data['parent'],
        "sequence" => 0
      ));
      $this->saveCategoryMeta($category, array('name' => $data['name']));
    }

    public function saveCategoryMeta($id, $data, $lang=false) {
      if(! $lang) {
        $lang = Localization::getCurrentLanguage();
      }
      $db = App::instance()->db;
      $db->where("id", $id);
      $cat = $db->getOne("collection_categories");
      if(strlen($cat['meta']) > 0) {
        $meta = json_decode($cat['meta']);
      } else {
        $meta = array();
      }
      if(is_array($meta) && array_key_exists($lang, $meta)) {
        $toSave = array_merge($meta[$lang], $data);
      } else {
        $toSave = array($lang => $data);
      }
      $db->where("id", $id);
      $db->update("collection_categories", array(
        "meta" => json_encode($toSave)
      ));
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

    public function fields() {
      $fields = array_merge($this->defaultFields(), $this->customFields);
      return array_msort($fields, array('order'=>SORT_ASC, 'key'=>SORT_ASC));
    }

  static public function instance() {
    $class = get_called_class();
    if(!array_key_exists($class, static::$instances)) {
      static::$instances[$class] = new $class();
    }
    static::$instances[$class]->init();
    return static::$instances[$class];
  }
  private function __construct() {}
  private function __clone() {}

}

?>
