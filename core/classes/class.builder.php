<?php

namespace Forge\Core\Classes;

use Forge\Core\App\App;
use Forge\Core\Classes\Localization;

class Builder {
    private $type = false;
    private $id = null;
    private $page = null;


    public function __construct($type = 'page', $id = null) {
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
            'elements' => $this->getElements(0)
        ]);
    }

    private function innerContentUrl($target) {
        return Utils::getUrl(array('manage', 'pages', 'edit', $this->page->id, 'add-element'), true, array('target' => $target));
    }

}

?>
