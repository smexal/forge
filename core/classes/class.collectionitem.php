<?php

namespace Forge\Core\Classes;

use Forge\Core\App\App;
use Forge\Core\Interfaces\ICollectionItem;

class CollectionItem implements ICollectionItem {
    public $id = null;

    private $db = null;
    private $base_data = null;
    private $meta = null;
    public $bodyclass = 'collection';

    public function __construct($id) {
        $this->id = $id;
        $this->db = App::instance()->db;

        $this->db->where('item', $this->id);
        $this->meta = $this->db->get('collection_meta');

        $this->db->where('id', $this->id);
        $this->base_data = $this->db->getOne('collections');

        $this->bodyclass.= ' '.$this->base_data['type'];
    }

    public function getCollection() {
        return App::instance()->cm->getCollection($this->base_data['type']);
    }

    public function getType() {
        return $this->base_data['type'];
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

    public function getSlug() {
        return $this->slug();
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
        if($lang === false) { // if lang is exactly '0', language independent field is requested.
            $lang = Localization::getCurrentLanguage();
        }
        foreach ($this->meta as $meta) {
            // return the value, if the meta value is language independent
            if($meta['keyy'] == $key && $meta['lang'] == 0) {
                if($meta['value'] === '0') {
                    return $meta['value'];
                }
                return Utils::maybeJSON($meta['value']);
            }
            // return the value, if the language and the key are the same.
            if($meta['keyy'] == $key && $meta['lang'] == $lang) {
                return Utils::maybeJSON($meta['value']);
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
        $deleted = false;
        if(strlen($value) == 0 || $value === '0') {
            // remove meta value, if there is no value
            $deleted = true;
            $this->deleteMeta($key, $language);
        }
        if($current_value) {
            // update with new
            $this->setMeta($key, $value, $language);
        } else {
            // insert new value
            if($deleted) {
                return;
            }
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

        if($this->isPublished() || $this->getAuthor() == App::instance()->user->get('id')) {
            $password_set = false;
            if((array_key_exists('password_protection', $_POST)
                && $_POST['password_protection'] == $this->getMeta('password_protection')) ||
                @$_SESSION['pw_'.$this->id] == md5($this->getMeta('password_protection'))) {
                $_SESSION['pw_'.$this->id] = md5($this->getMeta('password_protection'));
                $password_set = true;
            }
            if($this->getMeta('password_protection') && ! $password_set) {
                $body = $this->collectionLogin();
            } else {
                $body = $app->cm->getCollection($this->base_data['type'])->render($this);
            }
            return $app->render($app->tm->getTemplateDirectory(), "layout", array_merge(
                array(
                    'bodyclass' => $this->bodyclass,
                    'head' => $app->tm->theme->header(),
                    'body' => $body,
                    'messages' => App::instance()->displayMessages()
                ),
                $app->tm->theme->globals()
          ));
      }
      return App::instance()->redirect('denied');
  }

  private function collectionLogin() {
    $form = '<form method="post">'.Fields::text([
        'key' => 'password_protection',
        'label' => i('Password', 'core'),
        'type' => 'password'
    ]);
    $form.=Fields::button(i('Submit', 'core'));
    $form.='</form>';
    return App::instance()->render(CORE_TEMPLATE_DIR.'views/sites/', 'smallcenter-content', [
        'title' => i('Password protection', 'core'),
        'lead' => i('This page is password protected.', 'core'),
        'content' => $form
    ]);
  }
}

