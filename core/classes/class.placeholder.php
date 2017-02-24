<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

class Placeholder {
    private $elements = [];

    public function addBlock($width, $height = null) {
        if(!$height) {
            $height = '20px';
        }
        $this->elements[] = [$width, $height];
    }


    public function render() {
        $render = '';
        foreach ($this->elements as $el) {
            $render.= App::instance()->render(CORE_TEMPLATE_DIR."assets/", "placeholder", array(
                'width' => $el[0],
                'height' => $el[1]
            ));
        }
        return $render;
    }

}

?>