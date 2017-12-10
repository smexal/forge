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

    private $type = false;
    private $id = null;
    private $page = null;

    private $apiMainListener = 'forge-builder';

    public function __construct($type = 'page', $id = null) {
        $this->__swConstruct();

        $this->id = $id;
        if($type=='page' || $type == 'collection') {
            $this->type = $type;
        }
        if($type == 'page') {
            $this->page = new Page($id);
        }
        if(! $this->type) {
            throw new \Exception('Unknown Builder type.');
        }
        if(is_null($id)) {
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

    private function getElements($parent, $lang = null) {
        if(is_null($lang)) {
            $lang = Localization::getCurrentLanguage();
        }
        $elements = array();
        foreach( $this->page->getElements(0, $lang) as $element ) {
            array_push($elements, array(
                'name' => $element->getPref('name'),
                'type' => $element->getPref('id'),
                'id' => $element->id,
                'container' => $element->getPref('container'),
                'addcontent' => $element->getPref('container') ? $this->innerContentUrl($element->id) : '',
                'edit' => array(
                    'link' => Utils::getUrl(array('manage', 'pages', 'edit-element', $element->id)),
                    'name' => i('Edit')
                ),
                'remove' => array(
                    'link' => Utils::getUrl(array('manage', 'pages', 'remove-element', $element->id)),
                    'name' => i('Remove')
                ),
                'content' => $element->getBuilderContent()
            ));
        }
        return $elements;
    }

    public function render() {
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "builder", [
            'new_url' => Utils::getUrl(array('manage', 'pages', 'edit', $this->id, 'add-element'), true),
            'order_callback' => Utils::getUrl(['api', 'forge-builder', 'order-update']),
            'elements' => $this->getElements(0)
        ]);
    }

    private function innerContentUrl($target) {
        return Utils::getUrl(array('manage', 'pages', 'edit', $this->page->id, 'add-element'), true, array('target' => $target));
    }

}

?>
