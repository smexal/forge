<?php

namespace Forge\Core\Classes;

use Forge\Core\App\App;
use Forge\Core\Classes\Localization;
use Forge\Core\Interfaces\ICollectionItem;
use Forge\Core\Classes\Relations\Enums\DefaultRelations;
use Forge\Core\Classes\Relations\Enums\Prepares;

class CollectionItem implements ICollectionItem {
    const AS_ID = 0x1;
    const AS_ITEM = 0x2;

    // @deprecated Will be set private in the next major update. Use getID instead
    public $id = null;
    private $db = null;
    private $base_data = null;
    private $meta = null;
    public $bodyclass = 'collection';

    public function __construct($id) {
        $this->id = $id;
        $this->db = App::instance()->db;

        $curLang = Localization::getCurrentLanguage();
        $this->db->where('item', $this->id);
        $this->db->where('(lang = "'.$curLang.'" or lang = "0")');

        $this->meta = [];
        $metas = $this->db->get('collection_meta');
        foreach($metas as $set) {
            $this->meta[$set['keyy']] = $set;
        }

        $this->db->where('id', $this->id);
        $this->base_data = $this->db->getOne('collections');
        if(is_null($this->base_data))
            return;
        $this->bodyclass.= ' '.$this->base_data['type'];
    }

    public static function create($args, $metas=array(), $prepare=CollectionItem::AS_ID) {
        $db = App::instance()->db;

        if(!isset($args['author'])) {
            $args['author'] = -1;
        }
        $item_id = $db->insert('collections', array(
          'sequence' => 0,
          'name' => $args['name'],
          'type' => $args['type'],
          'author' => $args['author']
        ));

        if($prepare == CollectionItem::AS_ITEM ||
            count($metas) ||
            (isset($args['parent']) && is_numeric($args['parent']))
        ) {
            $item = new CollectionItem($item_id);
        }

        if(count($metas)) {
            $item->insertMultipleMeta($metas);
        }

        if(isset($args['parent']) && is_numeric($args['parent'])) {
            $item->setParent($args['parent']);
        }

        if($prepare == CollectionItem::AS_ITEM) {
            return $item;
        }
        return $item_id;
    }


    public function getID() {
        return $this->id;
    }

    public function getCollection() {
        return App::instance()->cm->getCollection($this->base_data['type']);
    }

    public function getType() {
        return $this->base_data['type'];
    }

    public function url($manage=false, $additional = []) {
        $parent = App::instance()->cm->getCollection($this->base_data['type']);
        if(! $parent) {
            return;
        }
        if(!$manage) {
            $params = array($parent->slug(), $this->slug());
        } else {
            $params = array('manage', 'collections', $parent->slug(), 'edit', $this->slug());
        }
        $params = array_merge([Localization::getCurrentLanguage()], $params, $additional);
        return Utils::getUrl($params);

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
        if(is_null($this->base_data))
            return;
        return $this->base_data['name'];
    }

    public function getAuthor() {
        if(is_null($this->base_data))
            return;
        return $this->base_data['author'];
    }

    public function getSequence() {
        if(is_null($this->base_data))
            return;
        return $this->base_data['sequence'];
    }

    public function getCreationDate() {
        if(is_null($this->base_data))
            return;
        return $this->base_data['created'];
    }

    public function getMeta($key, $lang = false) {
        if($lang === false) { // if lang is exactly '0', language independent field is requested.
            $lang = Localization::getCurrentLanguage();
        }
        foreach ($this->meta as $meta) {
            // return the value, if the meta value is language independent
            if($meta['keyy'] == $key && is_numeric($meta['lang'])) {
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

    public function deleteMeta($key, $language='0') {
        $this->db->where('keyy', $key);
        $this->db->where('item', $this->id);
        $this->db->where('lang', $language);
        $this->db->delete('collection_meta');
        unset($this->meta[$key]);
    }

    /**
     * Use this only when the meta field already exsists
     */
    public function setMeta($key, $value, $language='0') {
        $value = is_array($value) ? json_encode($value) : $value;
        $this->db->where('keyy', $key);
        $this->db->where('item', $this->id);
        $this->db->where('lang', $language);
        $this->db->update('collection_meta', array(
            'value' => $value
        ));
        $this->meta[$key] = [
            'keyy' => $key,
            'item' => $this->id,
            'value' => $value,
            'lang' => $language
        ];
    }

    public function setAuthor($newAuthor) {
        $this->db->where('id', $this->getID());
        $this->db->update('collections', [
            'author' => $newAuthor
        ]);
        $this->base_data['author'] = $newAuthor;
    }

    public function setSequence($newSequence) {
        $this->db->where('id', $this->getID());
        $this->db->update('collections', [
            'sequence' => $newSequence
        ]);
        $this->base_data['sequence'] = $newSequence;
    }

    public function insertMultipleMeta($metas) {
        foreach($metas as $key => &$value) {
            $value['item'] = $this->id;

            if(!isset($value['keyy']) && is_string($key)) {
                $value['keyy'] = $key;
            }

            if(!isset($value['lang'])) {
                $value['lang'] = '0';
            }

            if(is_array($value['value'])) {
                $value['value'] = json_encode($value);
            }
        }
        $this->db->insertMulti('collection_meta', $metas);
        $this->meta = array_merge($this->meta, $metas);
      }

      public function insertMeta($key, $value, $language) {
        if(strlen($value) == 0) {
            return;
            // don't save if we don't have anything to save...
        }
        $data = array(
            'keyy' => $key,
            'lang' => $language,
            'item' => $this->id,
            'value' => $value
        );
        $this->db->insert('collection_meta', $data);
        $this->meta[$key] = $data;
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
                // collection subview wanted, render this
                $parts = Utils::getUriComponents();
                $collectionObj = $app->cm->getCollection($this->base_data['type']);
                if(array_key_exists(3, $parts) && method_exists($collectionObj, $parts[3])) {
                    $method = $parts[3];
                    $body = $collectionObj->$method($this);
                } else {
                    $body = $collectionObj->render($this);
                }
            }
            $layout = 'layout';
            if($app->tm->theme->ajaxLayout && Utils::isAjax()) {
                $layout = $app->tm->theme->ajaxLayout;
            }
            return $app->render($app->tm->getTemplateDirectory(), $layout, array_merge(
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

  public function setParent($parent_id) {
    $relation = App::instance()->rd->getRelation(DefaultRelations::PARENT_OF);
    $this->removeParent();
    $relation->add($parent_id, $this->getID());
  }

  public function getParent($parent_id = false) {
    $relation = App::instance()->rd->getRelation(DefaultRelations::PARENT_OF);
    $ids = $relation->getOfRight($this->getID(), Prepares::AS_IDS_LEFT);
    if(!count($ids)) {
        return null;
    }
    return new CollectionItem($ids[0]);
  }

  public function removeParent() {
    $relation = App::instance()->rd->getRelation(DefaultRelations::PARENT_OF);
    $relation->removeByRightID($this->getID());
  }


  public function getChildren() {
    $relation = App::instance()->rd->getRelation(DefaultRelations::PARENT_OF);
    $id_list = $relation->getOfLeft($this->getID(), Prepares::AS_IDS_RIGHT);
    $list = [];
    foreach($id_list as $item_id) {
        $list[] = new CollectionItem($item_id);
    }
    return $list;
  }

  public function removeChildren($children) {
    $relation = App::instance()->rd->getRelation(DefaultRelations::PARENT_OF);
    $relation->removeAll($this->getID());
  }

  /**
   * Removes this item completely from the DB
   */
  public function delete() {
    $this->db->where('id', $this->getID());
    $this->db->delete('collections');

    $this->db->where('item', $this->getID());
    $this->db->delete('collection_meta');

    $this->meta = [];

    $relation = App::instance()->rd->getRelation(DefaultRelations::PARENT_OF);
    // Remove children relations
    $relation->removeAll($this->getID());
    // Remove parent relation
    $relation->removeByRightID($this->getID());
    \fireEvent('Forge/Core/CollectionItem/delete', $this, $this->meta);
  }

  // TODO: Remove this from collection item
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

