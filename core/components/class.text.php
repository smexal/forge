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
