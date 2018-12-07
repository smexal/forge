<?php

namespace Forge\Core\Components;

use \Forge\Core\Classes\CollectionItem;
use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\App\ModifyHandler;
use \Forge\Core\Classes\Logger;

abstract class ListingComponent extends Component {
    public $settings = array();
    protected $order = 'id';
    protected $orderDirection = 'DESC';
    protected $collection = null;
    protected $cssClasses = [];

    public function __construct() {
        Auth::registerPermissions("api.collection.".$this->collection.'.read');

        $this->settings = [
            [
                "label" => i('Title'),
                "hint" => '',
                "key" => "title",
                "type" => "text"
            ],
            [
                'label' => i('Choose Elements'),
                'hint' => i('If you want you can choose the desired objects for the listing.'),
                'key' => 'filter',
                'type' => 'collection',
                'collection' => $this->collection
            ]
        ];
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
        if(method_exists($this, 'beforeContent')) {
            $this->beforeContent();
        }
        $message = false;
        if(is_null($this->collection)) {
           $message = i('No Collection defined for listing, contact your administrator.', 'core'); 
           $items = false;
       }

        $collectionItems = [];
        if( strlen($this->getField('filter')) > 0 ) {
            $filter = explode(",", $this->getField('filter'));
            foreach($filter as $i) {
                $collectionItems[] = new CollectionItem($i);
            }
        }

        if(! $message && count($collectionItems) == 0) {
            $collection = App::instance()->cm->getCollection($this->collection);
            $collectionItems = $collection->items([
                'status' => 'published',
                'order' => $this->order,
                'order_direction' => $this->orderDirection
            ]);
        }

        $collectionItems = ModifyHandler::instance()->trigger(
            'modify_collection_listing_items',
            $collectionItems
        );

        $filter = false;
        if(method_exists($this, 'getFilter')) {
            $filter = $this->getFilter();
        }

        $items = [];
        foreach($collectionItems as $item) {
            array_push($items, $this->renderItem($item));
        }
        $items = array_reverse($items);

        $args = [
            'title' => $this->getField('title'),
            'filter' => $filter,
            'message' => $message,
            'items' => $items,
            'type' => $this->collection,
            'classes' => implode(" ", $this->cssClasses)
        ];
        $args = ModifyHandler::instance()->trigger(
            'modify_collection_listing_args',
            $args
        );

        $templateDir = CORE_TEMPLATE_DIR."components/";
        $templateDir = ModifyHandler::instance()->trigger(
            'modify_collection_listing_templateDir',
            $templateDir
        );

        $templateName = 'listing';
        $templateName = ModifyHandler::instance()->trigger(
            'modify_collection_listing_templateDir',
            $templateName
        );

        return App::instance()->render($templateDir, $templateName, $args);
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

