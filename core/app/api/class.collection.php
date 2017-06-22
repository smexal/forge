<?php

namespace Forge\Core\App\Api;

use \Forge\Core\App\App;
use \Forge\Core\App\API;
use \Forge\Core\App\CollectionManager;
use \Forge\Core\Abstracts\APIFacade;
use \Forge\Core\Classes\Utils;

/**
 * This class allows reading out collections in a crud manner via an APIFacade.
 * 
 * In order to allow access to a new collection the permissians have to be registered
 * via the Permissions / Authentication classes of the forge core.
 */
class Collection extends APIFacade {
  const PERM_C = 'c';
  const PERM_R = 'r';
  const PERM_U = 'u';
  const PERM_D = 'd';

  public $trigger = 'collections';

  protected static $permissions = [
    'c' => 'api.collection.%name%.create',
    'r' => 'api.collection.%name%.read',
    'u' => 'api.collection.%name%.update',
    'd' => 'api.collection.%name%.delete'
  ];

  protected static $uri_mapping = [
    'collection' => null,
    'id'         => null
  ];

  protected static $request_params = [
    // QUERY
    'q' => null,
    // STATE
    's' => 'published',
    // ORDER
    'o'  => 'name',
    // ORDER Direction
    'od' => 'ASC',
    // LIMIT Start
    'ls' => '0',
    // LIIMIT End
    'll' => '30',
    // INCLUDE extra infos about the items
    'e' => ''
  ];

  protected function __construct() {
  }


  public function call($request) {
    header("Content-Type: text/html");

    $method = $request['method'];
    $query = Utils::extractParams(static::$uri_mapping, $request['query']);
    $data = $this->extractData($request['data']);
    $call = [$this, strtolower($method)];

    if(!is_callable($call)) {
      API::error(405, i('Undefined collection method', 'forge'));
    }

    $response = \call_user_func_array($call, [$query, $data]);
    die(json_encode($response));
  }

  public function extractData($data) {
    $data = array_merge(static::$request_params, $data);
    $data['e'] = explode(',', $data['e']);
    return $data;
  }

  public function get($query, $data) {
    $c_name = $query['collection'];

    if(!$this->actionAllowed($c_name)) {
      API::error(401, sprintf(i('User is not allowed to read the collection %s', 'core'), $c_name));
    }

    if($data['s'] != 'published' && !$this->unpublishedStatusAllowed($c_name, $data['s'])) {
      API::error(401, sprintf(i('User is not allowed to read the collection for the provided status %s', 'core'), $c_name));
    }

    $dc_object = App::instance()->cm->getCollection($c_name);
    if(!$dc_object) {
      API::error(404, sprintf(i('Undefined collection type: %s', 'forge'), $c_name));
    }

    $collection = null;
    $c_id = $query['id'];
    // SINGLE COLLECTION
    if(!is_null($c_id)) {
      if(is_numeric($c_id)) {
        $collection = $dc_object->getItem($c_id);
      } else {
        $collection = $dc_object->getBySlug($c_id);
      }
      return $this->representSingle($collection, $data);
    }

    // MULTIPLE
    $items_query = $this->itemsQuery($data);
    $collections = $dc_object->items($items_query);

    return $this->representMultiple($collections, $data);
  }

  private function representSingle($item, $data) {
    $single = [
      'id' => $item->id,
      'url' => $item->url(),
      'ressource' => $this->getAPIRessourceURI($item),
      'slug' => $item->getSlug(),
      'name' => $item->getName(),
      'author' => $item->getAuthor(),
      'creationdate' => $item->getCreationDate(),
      'published' => $item->isPublished(),
    ];

    //metas:published|other
    if(false !== ($key = $this->hasExtra($data['e'], '/^metas\:.*/'))) {
      $tmp = explode(':', $data['e'][$key])[1];
      $list = explode('|', $tmp);

      $single['metas'] = [];
      foreach($list as $meta) {
        $single['metas'][$meta] = $item->getMeta($meta);
      }
    }

    return $single;
  }

  private function representMultiple($items, $data) {
    $multiple = [
      'items' => [],
      'meta' => [
        'count' => count($items),
        'start' => $data['ls'],
        'length' => $data['ll']
      ]
    ];
    foreach($items as $item) {
      $multiple['items'][] = $this->representSingle($item, $data);
    }
    return $multiple;
  }

  private function unpublishedStatusAllowed($name, $perm_key=Collection::PERM_R) {
    return true;
  }

  private function actionAllowed($name, $perm_key=Collection::PERM_R) {
    $permission = str_replace('%name%', $name, static::$permissions[$perm_key]);

    if(!App::instance()->user->allowed($permission)) {
      return false;
    }

    return true;

  }

  public function getAPIRessourceURI($item) {
    $uri = API::getAPIURL();
    $uri .= '/' . $this->trigger;
    $uri .= '/' . $item->getType();
    $uri .= '/' . $item->id;
    return $uri;
  }

  private function itemsQuery($data) {
    $query = [
      'order' => $data['o'],
      'order_direction' => $data['od'],
      'limit' => [$data['ls'],  $data['ll']]
    ];

    if($data['s'] != 'all') {
      $query['status'] = $data['s'];
    }
    if($data['q'] != 'all') {
      $query['query'] = $data['q'];
    }
    return $query;
  }

  private function hasExtra($extras, $pattern) {
    foreach($extras as $key => $value) {
      if(preg_match($pattern, $value)) {
        return $key;
      }
    }
    return false;
  }

}