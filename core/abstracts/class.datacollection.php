<?php

abstract class DataCollection implements IDataCollection {
  public $permission = null;
  protected static $instances = array();
  protected $app;
  public $preferences = array();
  public $name = false;
  private $customFields = array();
  private $customConfiguration = array();

  abstract protected function setup();

  public function getPref($name) {
    return $this->preferences[$name];
  }

  public static function savefield($item, $key, $value, $lang) {
      $item->updateMeta($key, $value, $lang);
  }

  public static function removefield($item, $key, $lang) {
    $item->updateMeta($key, '', $lang);
  }

  public function render($item) {
    return 'overwrite render method with $item';
  }

  private function init() {
    $this->app = App::instance();
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

  public function items($settings = array()) {
    $db = App::instance()->db;
    if(array_key_exists('order', $settings)) {
      $direction = 'asc';
      if(array_key_exists('order_direction', $settings)) {
        $direction = $settings['order_direction'];
      }
      $db->orderBy($settings['order'], $direction);
    }
    $limit = false;
    if(array_key_exists('limit', $settings)) {
      $limit = $settings['limit'];
    }
    $db->where('type', $this->name);
    if(! $limit) {
      $items = $db->get('collections');
    } else {
      $items = $db->get('collections', $limit);
    }
    $item_objects = array();
    foreach($items as $item) {
      $obj = new CollectionItem($item['id']);
      if(array_key_exists('status', $settings)) {
        if($settings['status'] == 'published' || $settings['status'] == 'draft') {
          if( $obj->getMeta('status') != $settings['status'] ) {
            continue;
          }
        }
      }

      array_push($item_objects, $obj);
    }

    return $item_objects;
  }

  public function slug() {
    $slug = $this->getSetting('slug');
    if(! is_null($slug)) {
      return $slug;
    } else {
      return $this->name;
    }
  }

  public function getBySlug($slug) {
    foreach($this->items() as $item) {
      $i = new CollectionItem($item->id);
      if($i->slug() == $slug) {
        return $i;
      }
    }
    return null;
  }

  public function getItem($id) {
    return new CollectionItem($id);
  }

  public function saveSetting($key, $value, $lang = false) {
    if($lang === false) {
      $lang = Localization::getCurrentLanguage();
    }
    $db = App::instance()->db;

    $db->where('type', $this->name);
    $db->where('keyy', $key);
    $db->where('lang', $lang);
    $val = $db->getOne('collection_settings');
    if($val) {
      $db->where('type', $this->name);
      $db->where('keyy', $key);
      $db->where('lang', $lang);
      $db->update('collection_settings', array(
        'value' => $value
      ));
    } else {
      $db->insert('collection_settings', array(
        'keyy' => $key,
        'value' => $value,
        'lang' => $lang,
        'type' => $this->name
      ));
    }
  }

  public function getSetting($key, $lang=false) {
    if(!$lang) {
      $lang = Localization::getCurrentLanguage();
    }
    $db = App::instance()->db;
    $db->where('type', $this->name);
    $db->where('keyy', $key);
    $db->where('lang', $lang);
    $setting = $db->getOne('collection_settings');
    return $setting['value'];
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
                  // remove field
                  self::removefield($item, $field['key'], $lang);
                  continue;
              }
              if($field['multilang'] == false) {
                  $lang = false;
              } else {
                  $lang = $data['language'];
              }
              if(is_array($data[$field['key']])) {
                $data[$field['key']] = json_encode($data[$field['key']]);
              }
              self::savefield($item, $field['key'], $data[$field['key']], $lang);
          }
      } else {
          App::instance()->addMessage(i('Unable to save item, Item does not exist'));
      }
  }

    private function defaultConfiguration() {
      $fields = array(
            array(
                'key' => 'slug',
                'label' => i('Slug', 'core'),      // default value is "Label"
                'multilang' => true,
                'type' => 'text',                   // default value is text
                'order' => 1,                       // default value is 1000
                'hint' => i('Will be used as part of the url for the detail view.', 'core')
            )
      );
      return $fields;
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
                    $this->preferences['single-item']
                )
            ),
            array(
                'key' => 'categories',
                'label' => i('Categories'),
                'multilang' => false,
                'type' => 'multiselect',
                'order' => 10,
                'position' => 'right',
                'values' => $this->getCategoriesForSelection(),
                'hint' => sprintf(
                    i('Select categories for this %1$s.'),
                    $this->preferences['single-item'])
            )
        );
        return $fields;
    }

    public function addConfigurations( $fields=array() ) {
        foreach($fields as $field) {
            if(is_array($field)) {
                $this->addConfiguration($field);
            }
        }
    }

    public function addConfiguration($field=array()) {
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
        if(! array_key_exists('hint', $field)) {
            $field['hint'] = false;
        }
        array_push($this->customConfiguration, $field);
    }

    public function addFields( $fields=array() ) {
        foreach($fields as $field) {
            if(is_array($field)) {
                $this->addField($field);
            }
        }
    }

    private function getCategoriesForSelection($parent = 0, $level = 0) {
      $returnable = array();
      $cats = $this->getCategories($parent);
      foreach($cats as $cat) {
        $meta = $this->getCategoryMeta($cat['id']);
        $indent = str_repeat("&nbsp;&nbsp;", $level);
        $returnable[] = array(
          'value' => $cat['id'],
          'active' => false,
          'text' => $indent.$meta->name
        );
        $returnable = array_merge($returnable, $this->getCategoriesForSelection($cat['id'], $level+1));
      }
      return $returnable;
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
      if(@$json->$lang) {
        return $json->$lang;
      } else {
        // not found in this language. get in other.
        foreach(Localization::getActiveLanguages() as $lang) {
          if(@$json->$lang['code']) {
            return $json->$lang['code'];
          }
        }
      }
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

    public function configuration() {
      $fields = array_merge($this->defaultConfiguration(), $this->customConfiguration);
      return array_msort($fields, array('order'=>SORT_ASC, 'key'=>SORT_ASC));
    }

    public function fields() {
      $fields = array_merge($this->defaultFields(), $this->customFields);
      return array_msort($fields, array('order'=>SORT_ASC, 'key'=>SORT_ASC));
    }

  static public function instance() {
    $class = get_called_class();
    if(!array_key_exists($class, static::$instances)) {
      static::$instances[$class] = new $class();
      static::$instances[$class]->init();
    }
    return static::$instances[$class];
  }
  private function __construct() {}
  private function __clone() {}

}

?>
