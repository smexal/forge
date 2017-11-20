<?php

namespace Forge\Core\Abstracts;

use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\CollectionItem;
use \Forge\Core\Classes\FieldSaver;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Logger;
use \Forge\Core\Interfaces\IDataCollection;

abstract class DataCollection implements IDataCollection {
    public $permission = null;
    protected static $instances = array();
    protected $app;
    public $preferences = array();
    public $name = false;
    protected $customFields = array();
    private $customConfiguration = array();

    abstract protected function setup();

    public function getPref($name) {
        return $this->preferences[$name];
    }

    public function render($item) {
        return 'overwrite render method with $item';
    }

    public function getSubnavigation() {
        return false;
    }

    private function getSubviewName($name) {
        $ex = explode("-", $name);
        for ($x = 0; $x < count($ex); $x++) {
            $ex[$x] = ucfirst($ex[$x]);
        }
        return implode("", $ex);
    }

    public function getSubview($view, $item) {
        $method = "subview".$this->getSubviewName($view);
        if (method_exists($this, $method)) {
            return $this->$method($item);
        }
        return 'no subview method found: \"'.$method.'\"';
    }

    public function getSubviewActions($view, $item) {
        $method = "subview".$this->getSubviewName($view).'Actions';
        if (method_exists($this, $method)) {
            return $this->$method($item);
        } else {
            return 'nf';
        }
    }

    private function init() {
        $this->app = App::instance();
        if (!is_null($this->permission)) {
            Auth::registerPermissions($this->permission);
        }

        $this->preferences = array(
            'name' => strtolower(get_class($this)),
            'title' => i('Data'),
            'all-title' => i('All Collection Items'),
            'add-label' => i('Add item'),
            'single-item' => i('item'),
            'multilang' => false,
            'has_status' => false,
            'has_categories' => false,
            'has_password' => false,
            'has_image' => false
        );
        $this->setup();
        $this->name = $this->getPref('name');
    }

    public function customEditContent($id) {
        return false;
    }

    /**
     * Fetch collectionitems based on the provided filter params
     *
     * @param $settings['meta_query'] | Associative array with key = meta_key and value = meta_value
     */
    public function items($settings = array()) {
        $db = App::instance()->db;
        if (array_key_exists('order', $settings)) {
            $direction = 'asc';
            if(array_key_exists('order_direction', $settings)) {
                $direction = $settings['order_direction'];
            }
            $db->orderBy($settings['order'], $direction);
        }
        $limit = false;
        if (array_key_exists('limit', $settings)) {
            $limit = $settings['limit'];
        }
        $db->where('type', $this->name);

        if(array_key_exists('query', $settings)) {
            $db->where('name', $db->escape($settings['query']), 'LIKE');
        }

        if(array_key_exists('meta_query', $settings)) {
            $idx = -1;
            foreach($settings['meta_query'] as $key => $value) {
                $idx++;
                $as_key = "cm_{$idx}";
                $db->join("collection_meta $as_key", "collections.id = {$as_key}.item", 'RIGHT');
                $db->where("{$as_key}.keyy", $key);
                $db->where("{$as_key}.value", $value);
            }
        }

        if (!$limit) {
            $items = $db->get('collections');
        } else {
            $items = $db->get('collections', $limit);
        }

        $item_objects = array();
        foreach ($items as $item) {
            $obj = new CollectionItem($item['id']);
            if (array_key_exists('status', $settings)) {
                if ($settings['status'] == 'published' || $settings['status'] == 'draft') {
                    if ($obj->getMeta('status') != $settings['status']) {
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
        if (! is_null($slug)) {
            return $slug;
        } else {
            return $this->name;
        }
    }

    public function getBySlug($slug) {
        foreach ($this->items() as $item) {
            $i = new CollectionItem($item->id);
            if ($i->slug() == $slug) {
                return $i;
            }
        }
        return null;
    }

    public function getItem($id) {
        return new CollectionItem($id);
    }

    public function getItems($items) {
        $list = [];
        foreach($items as $id) {
            $list[$id] = new CollectionItem($id);
        }

        return $list;
    }

    public function saveSetting($key, $value, $lang = false) {
        if ($lang === false) {
            $lang = Localization::getCurrentLanguage();
        }
        $db = App::instance()->db;

        $db->where('type', $this->name);
        $db->where('keyy', $key);
        $db->where('lang', $lang);
        $val = $db->getOne('collection_settings');
        if ($val) {
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
        if (!$lang) {
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
      if (array_key_exists('itemid', $data)) {
          $item = new CollectionItem($data['itemid']);
          if (array_key_exists('lang', $data)) {
              App::instance()->addMessage(i('Language for saving values for page not set.'));
              return;
          }

          foreach ($this->fields($item) as $field) {
              if (!array_key_exists($field['key'], $data)) {
                  // remove field
                  FieldSaver::remove($item, $field, isset($data['lang']) ? $data['lang'] : 0);
                  continue;
              }
              FieldSaver::save($item, $field, $data);
          }
         \fireEvent('/Forge/Core/DataCollection/save', $item);
      } else {
          App::instance()->addMessage(i('Unable to save item, Item does not exist'));
      }
    }

    private function defaultConfiguration() {
      $fields = array(
            array(
                'key' => 'slug',
                'label' => i('Slug', 'core'),       // default value is "Label"
                'multilang' => true,
                'type' => 'text',                   // default value is text
                'order' => 1,                       // default value is 1000
                'hint' => i('Will be used as part of the url for the detail view.', 'core')
            )
      );
      return $fields;
    }

    private function defaultFields() {
        $fields = [];
        array_push($fields, array(
                'key' => 'title',
                'label' => i('Title', 'core'),      // default value is "Label"
                'multilang' => true,
                'type' => 'text',                   // default value is text
                'order' => 2,                       // default value is 1000
                'position' => 'left',               // default is left
                'hint' => i('Will be used for title attribute (Search Engine and Social Media Title)')
            ));
        array_push($fields, array(
                'key' => 'description',
                'label' => i('Description', 'core'),
                'multilang' => true,
                'type' => 'text',
                'order' => 3,
                'position' => 'left',
                'hint' => i('Will be used for description for Search Engines and Social Media')
            ));
        if($this->preferences['has_status']) {
            array_push($fields, array(
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
            ));
        }
        array_push($fields, array(
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
            ));
        if($this->preferences['has_categories']) {
            array_push($fields, array(
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
            ));
        }
        if($this->preferences['has_password']) {
            array_push($fields, array(
                'key' => 'password_protection',
                'label' => i('Password protection'),
                'multilang' => false,
                'type' => 'text',
                'order' => 20,
                'position' => 'right',
                'hint' => i('If you define a password, the detail page of this item will be protected by this password.')
            ));
        }
        if($this->preferences['has_image']) {
            array_push($fields, array(
                'key' => 'collection_image',
                'label' => i('Collection Image'),
                'multilang' => false,
                'type' => 'image',
                'order' => 30,
                'position' => 'right',
                'hint' => i('Choose an image for this collection item.')
            ));
        }
        return $fields;
    }

    protected function itemDependentFields($item) {}

    public function addConfigurations( $fields=array() ) {
        foreach($fields as $field) {
            if(is_array($field)) {
                $this->addConfiguration($field);
            }
        }
    }

    public function addConfiguration($field=array()) {
        if (! array_key_exists('key', $field)) {
            Logger::debug('<key> for field not set: '.implode(", ", $field));
            return;
        }
        if (! array_key_exists('multilang', $field)) {
            $field['multilang'] = true;
        }
        if (! array_key_exists('label', $field)) {
            $field['label'] = i('Label');
        }
        if (! array_key_exists('type', $field)) {
            $field['label'] = 'text';
        }
        if (! array_key_exists('order', $field)) {
            $field['order'] = 1000;
        }
        if (! array_key_exists('hint', $field)) {
            $field['hint'] = false;
        }
        array_push($this->customConfiguration, $field);
    }

    /**
     * No duplicate check is performed here.
     * If necessary
     */
    public function addFields( $fields=array() ) {
        foreach ($fields as $field) {
            if (is_array($field)) {
                $this->addField($field);
            }
        }
    }

    /**
     * This ensures that a field key is only once inside the field list.
     * 
     * Use this function inside the method itemDependentFields
     */
    public function addUniqueFields($fields=array()) {
        foreach ($fields as $field) {
            if (!is_array($field)) {
                Logger::debug('<field> is not an array');
                continue;
            }
            if(-1 === ($idx = $this->getFieldIdx($field['key']))) {
                $this->addField($field);
            } else {
                $this->replaceField($idx, $field);
            }
        }
    }

    protected function getFieldIdx($key) {
        foreach($this->customFields as $idx => $field) {
            if($field['key'] == $key) {
                return $idx;
            }
        }
        return -1;
    }

    protected function replaceField($idx, $field) {
        $field = $this->prepareField($field);
        $this->customFields[$idx] = $field;
    }


    private function getCategoriesForSelection($parent = 0, $level = 0) {
        $returnable = array();
        $cats = $this->getCategories($parent);
        foreach ($cats as $cat) {
            $meta = $this->getCategoryMeta($cat['id']);
            $indent = str_repeat("&nbsp;&nbsp;", $level);
            $returnable[] = [
                'value' => $cat['id'],
                'active' => false,
                'text' => $indent.$meta->name
            ];
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
        if (!$lang) {
            $lang = Localization::getCurrentLanguage();
        }
        $db = App::instance()->db;
        $db->where('id', $id);
        $cat = $db->getOne('collection_categories');
        $json = json_decode($cat['meta']);
        if (! @is_null($json->$lang)) {
            return $json->$lang;
        } else {
            // not found in this language. get in other.
            foreach (Localization::getActiveLanguages() as $al) {
                if (!is_null($json->{$al['code']})) {
                    return $json->{$al['code']};
                }
            }
        }
    }

    public function addCategory($data) {
        if (!array_key_exists("name", $data)) {
            $data['name'] = "(no name)";
        }
        if (!array_key_exists("parent", $data)) {
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
          if (! $lang) {
              $lang = Localization::getCurrentLanguage();
          }
          $db = App::instance()->db;
          $db->where("id", $id);
          $cat = $db->getOne("collection_categories");
          if (strlen($cat['meta']) > 0) {
              $meta = json_decode($cat['meta']);
          } else {
              $meta = array();
          }
          if (is_array($meta) && array_key_exists($lang, $meta)) {
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
        $field = $this->prepareField($field);
        array_push($this->customFields, $field);
    }

    protected function prepareField($field) {
        if (! array_key_exists('key', $field)) {
            Logger::debug('<key> for field not set: '.implode(", ", $field));
            return;
        }
        if (! array_key_exists('multilang', $field)) {
            $field['multilang'] = true;
        }
        if (! array_key_exists('label', $field)) {
            $field['label'] = i('Label');
        }
        if (! array_key_exists('type', $field)) {
            $field['type'] = 'text';
        }
        if (! array_key_exists('order', $field)) {
            $field['order'] = 1000;
        }
        if (! array_key_exists('position', $field)) {
            $field['position'] = 'left';
        }
        if (! array_key_exists('hint', $field)) {
            $field['hint'] = false;
        }
        return $field;
    }

    public function configuration() {
        $fields = array_merge($this->defaultConfiguration(), $this->customConfiguration);
        return array_msort($fields, array('order'=>SORT_ASC, 'key'=>SORT_ASC));
    }

    public function fields($item=null) {
      $this->itemDependentFields($item);
      $fields = array_merge($this->defaultFields(), $this->customFields);

      return array_msort($fields, array('order'=>SORT_ASC, 'key'=>SORT_ASC));
    }

    static public function instance() {
        $class = get_called_class();
        if (!array_key_exists($class, static::$instances)) {
            static::$instances[$class] = new $class();
            static::$instances[$class]->init();
        }
        return static::$instances[$class];
    }
    private function __construct() {}
    private function __clone() {}

}

