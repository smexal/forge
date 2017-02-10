<?php

namespace Forge\Core\Components;

use Forge\Core\App\CollectionManager;
use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;
use function \Forge\Core\Classes\i;

abstract class Listing extends Component {
    public $settings = array();
    protected $collection = null;

    public function __construct() {
        $this->settings = array(
            array(
                "label" => i('Title'),
                "hint" => '',
                "key" => "title",
                "type" => "text"
            )
        );
    }

    public function prefs() {
        return array(
            'name' => i('Listing'),
            'description' => i('Listing for a Collection'),
            'id' => 'listing',
            'image' => '',
            'level' => 'inner',
            'container' => false
        );
    }

    public function content() {
        $message = false;
        if(is_null($this->collection)) {
           $message = i('No Collection defined for listing, contact your administrator.', 'core'); 
           $items = false;
        } else {
            $collection = App::instance()->cm->getCollection($this->collection);
            $items = [];
            foreach($collection->items([
                'status' => 'published'
            ]) as $item) {
                array_push($items, $this->renderItem($item));
            }
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."components/", "listing", array(
            'message' => $message,
            'items' => $items
        ));
    }

    public function renderItem($item) {
        return App::instance()->render(CORE_TEMPLATE_DIR.'components/parts/', 'listing-item', array(
                    'title' => $item->getMeta('title'),
                    'description' => $item->getMeta('description')
        ));
    }

    public function customBuilderContent() {
        return App::instance()->render(CORE_TEMPLATE_DIR."components/builder/", "text", array(
            'text' => $this->shorten($this->getField('content'))
        ));
    }

    private function shorten($text='') {
        $text = strip_tags($text);
        if(strlen($text) >= 150) {
            return substr($text, 0, 150)."...";
        } else {
            return $text;
        }
    }

}

?>
