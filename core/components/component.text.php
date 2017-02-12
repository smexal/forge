<?php

namespace Forge\Core\Components;

use Forge\Core\App\CollectionManager;
use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;

class TextComponent extends Component {
    public $settings = array();

    public function prefs() {
        $this->settings = array(
            array(
                "label" => '',
                "hint" => '',
                "key" => "content",
                "type" => "wysiwyg"
            )
        );
        return array(
            'name' => i('Text'),
            'description' => i('Normal WYSIWYG Text Element'),
            'id' => 'text',
            'image' => '',
            'level' => 'inner',
            'container' => false
        );
    }

    public function content() {
        return App::instance()->render(CORE_TEMPLATE_DIR."components/", "text", array(
            'content' => $this->getField('content')
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

