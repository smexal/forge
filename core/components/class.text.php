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

}

?>
