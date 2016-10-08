<?php

class ComponentHtml extends Component {
    public $settings = array();

    public function prefs() {
        $this->settings = array(
            array(
                "label" => i('Insert your custom HTML'),
                "hint" => '',
                "key" => "html",
                "type" => "textarea"
            )
        );
        return array(
            'name' => i('HTML'),
            'description' => i('Add Custom HTML'),
            'id' => 'html',
            'image' => '',
            'level' => 'inner',
            'container' => false
        );
    }

    public function content() {
        return $this->getField('html');
    }

    public function customBuilderContent() {
        return App::instance()->render(CORE_TEMPLATE_DIR."components/builder/", "text", array(
            'text' => i('Custom HTML')
        ));
    }

}

?>
