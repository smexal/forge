<?php

class ComponentText extends Component {
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

    public function customBuilderContent() {
        Logger::debug($this->getField('content'));
        return App::instance()->render(CORE_TEMPLATE_DIR."components/builder/", "text", array(
            'text' => $this->shorten($this->getField('content'))
        ));
    }

    private function shorten($text='') {
        $text = strip_tags($text);
        if(strlen($text > 100)) {
            return substr($text, 0, 100)."...";
        } else {
            return $text;
        }
    }

}

?>
