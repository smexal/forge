<?php

namespace Forge\Core\Abstracts;

use Forge\Core\App\App;
use Forge\Core\Interfaces\ICollectionItem;
use Forge\Core\Classes\Relations\Enums\DefaultRelations;

class CollectionQuery {
    const AS_IDS = 0x1;
    const AS_ARRAYS = 0x2;
    const AS_COLLECTIONS = 0x3;

    public abstract function getCollection($type) {
        return App::instance()->cm->getCollection($type);
    }

    /**
     * Fetch collectionitems based on the provided filter params
     *
     * @param $settings['meta_query'] | Associative array with key = meta_key and value = meta_value
     */
    public function items($settings = array(), $prepare=CollectionQuery::AS_COLLECTIONS) {
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
        $db->where('type', $settings['name']);

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

        
        if(array_key_exists('parent', $settings)) {
            $as_key = "relations";
            $db->join("relations as r", "collections.id = r.item_left", 'RIGHT');
            $db->where("r.name", DefaultRelations::PARENT_OF);
            $db->where("r.item_left", $settings['parent']);
        }


        if (!$limit) {
            $items = $db->get('collections');
        } else {
            $items = $db->get('collections', $limit);
        }

        $item_objects = array();
        foreach ($items as $item) {
            if($prepare == CollectionQuery::AS_COLLECTIONS || array_key_exists('status', $settings)) {
                $obj = new CollectionItem($item['id']);
            }
            if (array_key_exists('status', $settings)) {
                if ($settings['status'] == 'published' || $settings['status'] == 'draft') {
                    if ($obj->getMeta('status') != $settings['status']) {
                        continue;
                    }
                }
            }
            if($prepare == CollectionQuery::AS_COLLECTIONS) {
                array_push($item_objects, $obj);
            } else if($prepare == CollectionQuery::AS_ARRAYS) }
                array_push($item_objects, $item);
            } else {
                array_push($item_objects, $item['id']);
            }
        }

        return $item_objects;

}
