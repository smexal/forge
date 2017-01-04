<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

class ContentNavigation {
    private $positions = array();

    static private $instance = null;

    public static function create($name, $position) {
        $db = App::instance()->db;
        $db->insert('navigations', array(
            'name' => $name,
            'position' => $position
        ));
        return false;
    }

    public static function update($id, $name, $position) {
        $db = App::instance()->db;
        $db->where('id', $id);
        $db->update('navigations', array(
            'name' => $name,
            'position' => $position
        ));
        return false;
    }

    public static function delete($id) {
        $db = App::instance()->db;
        $db->where('id', $id);
        $db->delete('navigations');
        $db->where('navigation_id', $id);
        $db->delete('navigation_items', $id);
        return false;
    }

    public static function getById($id) {
        $db = App::instance()->db;
        $db->where('id', $id);
        return $db->getOne('navigations');
    }

    public static function getByPosition($position) {
        $db = App::instance()->db;
        $db->where('position', $position);
        return $db->getOne('navigations');
    }

    public static function addItem($navigation, $data) {
        if(!array_key_exists('lang', $data)) {
            $data['lang'] = Localization::getCurrentLanguage();
        }
        $db = App::instance()->db;
        $data = array(
            "name" => $data['name'],
            "item_id" => $data['item'],
            "item_type" => $data['item_type'],
            "navigation_id" => $navigation,
            "order" => 0,
            "parent" => $data['parent'],
            "lang" => $data['lang']
        );
        $db->insert("navigation_items", $data);
    }

    public static function updateItem($item_id, $data) {
        if(!array_key_exists('lang', $data)) {
            $data['lang'] = Localization::getCurrentLanguage();
        }
        $db = App::instance()->db;
        $db->where('id', $item_id);
        $data = array(
            "name" => $data['name'],
            "item_id" => $data['item'],
            "item_type" => $data['item_type'],
            "order" => 0,
            "parent" => $data['parent'],
            "lang" => $data['lang']
        );
        $db->update("navigation_items", $data);
    }

    public static function deleteItem($id) {
        App::instance()->db->where('id', $id);
        App::instance()->db->delete('navigation_items');

        App::instance()->db->where('parent', $id);
        App::instance()->db->update('navigation_items', array(
            "parent" => 0
        ));
        return true;
    }

    public static function getItem($id) {
        App::instance()->db->where('id', $id);
        return App::instance()->db->getOne('navigation_items');
    }

    public static function getNavigation($position) {
        return self::getNavigationItemsByPosition($position);
    }
    public static function getNavigationItemsByPosition($position) {
        $nav = self::getByPosition($position);
        return self::getNavigationItems($nav['id']);
    }

    public static function getNavigationList($position) {
        $nav = self::getByPosition($position);
        $return = '';
        if(! is_null($nav)) {
            $return = '<nav class="'.$position.'">';
            $return.= self::getNavigationItems($nav['id'], false, 0, false, true);
            $return.= '</nav>';
        }
        return $return;
    }

    public static function getNavigationItems($navigation, $lang=false, $parent=0, $flat=false, $list = false) {
        if(!$list) {
            $items = array();
        } else {
            $list = '<ul>';
        }
        if(!$lang) {
            $lang = Localization::getCurrentLanguage();
        }
        $db = App::instance()->db;
        $db->where('navigation_id', $navigation);
        $db->where('lang', $lang);
        if(!$flat) {
            $db->where('parent', $parent);
        }
        foreach($db->get('navigation_items') as $item) {
            if(!$flat && ! $list) {
                $item['items'] = self::getNavigationItems($navigation, $lang, $item['id']);
            }
            if(!$list) {
                array_push($items, $item);
            } else {
                $list.='<li class="item-'.$item['item_id'].'">';
                if($item['item_type'] == 'page') {
                    $page = new Page($item['item_id']);
                    $link = $page->getUrl();
                } else if($item['item_type'] == 'view') {
                    $vm = App::instance()->vm;
                    $parts = explode("/", $item['item_id']);
                    $view = $vm->getViewByName($parts[0]);
                    if($view) {
                        $link = $view->buildURL();
                        if(array_key_exists(1, $parts)) {
                            $link.='/'.$parts[1];
                        }
                    } else {
                        Logger::debug('Could not find view'. $item['item_id']);
                        $link = '#';
                    }
                } else  {
                    $collectionItem = new CollectionItem($item['item_id']);
                    $link = $collectionItem->url();
                }
                $list.='<a href="'.$link.'">';
                $list.=$item['name'];
                $list.='</a>';
                $list.= self::getNavigationItems($navigation, $lang, $item['id'], false, true);
                $list.='</li>';
            }
        }
        if(!$list) {
            return $items;
        } else {
            $list.='</ul>';
            return $list;
        }
    }

    public static function registerPosition($id, $name) {
        $inst = self::instance();
        $inst->positions[$id] = $name;
    }

    public static function getNavigations() {
        $db = App::instance()->db;
        return $db->get('navigations');
    }

    public static function getPositions() {
        $inst = self::instance();
        return $inst->positions;
    }

    static public function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct(){}
    private function __clone(){}

}

?>
