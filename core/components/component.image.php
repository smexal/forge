<?php

namespace Forge\Core\Components;

use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Media;

class ImageComponent extends Component {
    public $settings = array();

    public function prefs() {
        $this->settings = array(
            array(
                "label" => '',
                "hint" => '',
                "key" => "image",
                "type" => "image"
            )
        );
        return array(
            'name' => i('Image'),
            'description' => i('Add an image.'),
            'id' => 'image',
            'image' => '',
            'level' => 'inner',
            'container' => false
        );
    }

    public function content() {
        $media = new Media($this->getField('image'));
        return App::instance()->render(CORE_TEMPLATE_DIR."components/", "image", array(
            'src' => $media->getUrl()
        ));
    }

    public function customBuilderContent() {
        $media = false;
        if($this->getField('image')) {
            $media = new Media($this->getField('image'));
        } else {
            $text = i('No image selected');
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."components/builder/", "text", array(
            'text' => ($media ? '<img src="'. $media->getUrl().'" width="40" />' : $text)
        ));
    }

}

