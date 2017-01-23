<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

use function \Forge\Core\Classes\i;

class CollectionItem {
    public $id = null;

    private $db = null;
    private $base_data = null;
    private $meta = null;

    public function __construct($id) {
        $this->id = $id;
        $this->db = App::instance()->db;

        $this->db->where('item', $this->id);
        $this->meta = $this->db->get('collection_meta');

        $this->db->where('id', $this->id);
        $this->base_data = $this->db->getOne('collections');
    }

    public function getCollection() {
        return App::instance()->cm->getCollection($this->base_data['type']);
    }

    public function url() {
        $parent = App::instance()->cm->getCollection($this->base_data['type']);
        return Utils::getUrl(array($parent->slug(), $this->slug()));
    }

    public function absUrl() {
        $uri = $this->url();
        return Utils::getAbsoluteUrlRoot().$uri;
    }

    public function slug() {
        $slug = $this->getMeta('slug');
        if($slug) {
            return $slug;
        } else {
            return $this->base_data['id'];
        }
    }

    public function getName() {
        return $this->base_data['name'];
    }

    public function getAuthor() {
        return $this->base_data['author'];
    }

    public function getCreationDate() {
        return $this->base_data['created'];
    }

    public function getMeta($key, $lang = false) {
        if(!$lang && $lang !== 0) {
            $lang = Localization::getCurrentLanguage();
        }
        foreach ($this->meta as $meta) {
            if ($meta['keyy'] == $key) {
                if ($meta['lang'] != 0) {
                    if ($lang !== false ? $lang === $meta['lang'] : $meta['lang'] == Localization::getCurrentLanguage()) {
                        return Utils::maybeJSON($meta['value']);
                    }
                } else {
                    return Utils::maybeJSON($meta['value']);
                }
            }
        }
        return false;
    }

    public function updateMeta($key, $value, $language) {
        if(!$language) {
            $language = 0;
        }
        $current_value = $this->getMeta($key, $language);
        if(is_array($current_value)) {
            $current_value = json_encode($current_value);
        }
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

    public function deleteMeta($key, $language) {
        $this->db->where('keyy', $key);
        $this->db->where('item', $this->id);
        $this->db->where('lang', $language);
        $this->db->delete('collection_meta');
    }

    public function setMeta($key, $value, $language) {
        $this->db->where('keyy', $key);
        $this->db->where('item', $this->id);
        $this->db->where('lang', $language);
        $this->db->update('collection_meta', array(
            'value' => $value
        ));
    }

      public function insertMeta($key, $value, $language) {
        if(strlen($value) == 0) {
            return;
            // don't save if we don't have anything to save...
        }
        $this->db->insert('collection_meta', array(
            'keyy' => $key,
            'lang' => $language,
            'item' => $this->id,
            'value' => $value
        ));
    }

    public function isPublished() {
        if($this->getMeta('status') == 'published') {
            return true;
        }
        return;
    }

    public function render() {
        $app = App::instance();

        // run theme methods..
        $app->tm->theme->styles();

        if($this->isPublished()) {
            return $app->render($app->tm->getTemplateDirectory(), "layout", array_merge(
                array(
                    'bodyclass' => '',
                    'head' => $app->tm->theme->header(),
                    'body' => $app->cm->getCollection($this->base_data['type'])->render($this),
                    'messages' => App::instance()->displayMessages()
                ),
                $app->tm->theme->globals()
          ));
      }
      return i('Access Denied');
  }
}

?>
