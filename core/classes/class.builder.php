<?php

namespace Forge\Core\Classes;

use Forge\Core\App\App;
use Forge\Core\Classes\Localization;
use Forge\Core\Traits\ApiAdapter;
use Forge\Core\Traits\Singleton;

class Builder {
    use ApiAdapter {
        ApiAdapter::__construct as private __swConstruct;
    }

    private $db = null;
    private $type = false;
    private $id = null;
    private $page = null;
    private $builderId = false;

    private $apiMainListener = 'forge-builder';

    public function __construct($type = 'page', $id = null, $builderId = false) {
        $this->builderId = $builderId;
        $this->__swConstruct();
        $this->db = App::instance()->db;

        $this->id = $id;
        if($type=='page' || $type == 'collection') {
            $this->type = $type;
        }
        if($type == 'page') {
            $this->page = new Page($this->id);
        }
        if(! $this->type) {
            throw new \Exception('Unknown Builder type.');
        }
        if(is_null($this->id)) {
            throw new \Exception('Unknown ID');
        }
    }

    public function orderUpdate() {
        $items = $_POST['itemset'];
        // update page elements
        $db = App::instance()->db;
        foreach($items as $item) {
            $db->where('id', $item['id']);
            $db->update('page_elements', [
                'position' => $item['order']
            ]);
        }
        return json_encode(['order' => 'updated']);
    }

  public function getBuilderElements($lang) {
      if($this->builderId !== false) {
        $this->db->where('builderId', $this->builderId);
      } else {
        $this->db->where('builderId', 'none');
      }
      $this->db->where('lang', $lang);
      $this->db->where('pageid', $this->id);
      $this->db->where('elementid != "row"');
      $this->db->orderBy('position', 'ASC');


      $elements = array();
      foreach($this->db->get('page_elements') as $element) {
          $element = App::instance()->com->instance($element['id'], $element['elementid']);
          if(!is_null($element)) {
              array_push($elements, $element);
          }
      }
      return $elements;
  }

    private function getElements($parent, $lang = null) {
        if(is_null($lang)) {
            $lang = Localization::getCurrentLanguage();
        }
        $elements = array();
        $fromParts = implode(",", Utils::getUriComponents());
        foreach( $this->getBuilderElements($lang) as $element ) {
            if($element->getPref('id') == "row") {
              continue;
            }
            array_push($elements, array(
                'name' => $element->getPref('name'),
                'custom' => $element->customBuilderContent(),
                'type' => $element->getPref('id'),
                'id' => $element->id,
                'container' => $element->getPref('container'),
                'addcontent' => $element->getPref('container') ? $this->innerContentUrl($element->id) : '',
                'edit' => array(
                    'link' => Utils::getUrl(
                        ['manage', 'pages', 'edit-element', $element->id],
                        true,
                        ['fromParts' => $fromParts]
                    ),
                    'name' => i('Edit')
                ),
                'remove' => array(
                    'link' => Utils::getUrl(
                        ['manage', 'pages', 'remove-element', $element->id],
                        true,
                        ['fromParts' => $fromParts]
                    ),
                    'name' => i('Remove')
                ),
                'content' => $element->getBuilderContent()
            ));
        }
        return $elements;
    }

  public function addElement($elementId, $type, $language, $parent=0, $position="end", $position_x = 0, $builderId = 'none') {
      $data = array(
          'pageid' => $elementId,
          'elementid' => $type,
          'prefs' => '',
          'parent' => $parent,
          'lang' => $language,
          'position' => $position == 'end' ? $this->getNextElementPosition($parent, $language, $position_x) : $position,
          'position_x' => $position_x,
          'builderId' => $builderId
      );
      $this->db->insert('page_elements', $data);
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
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "builder", [
            'builderTitle' => i('Components Builder', 'core'),
            'new_url' => Utils::getUrl(
                array('manage', 'pages', 'edit', $this->id, 'add-element'),
                true,
                [
                    'elementId' => $this->id,
                    'builderId' => $this->builderId,
                    'fromParts' => implode(",", Utils::getUriComponents())
                ]
            ),
            'order_callback' => Utils::getUrl(['api', 'forge-builder', 'order-update']),
            'elements' => $this->getElements(0)
        ]);
    }

    private function innerContentUrl($target) {
        return Utils::getUrl(array('manage', 'pages', 'edit', $this->page->id, 'add-element'), true, array('target' => $target));
    }

}

?>
