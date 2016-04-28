<?php

class ComponentRow extends Component {

    public function prefs() {
        return array(
            'name' => i('Row'),
            'description' => i('Add a row, in which you are able to place other elements.'),
            'id' => 'row',
            'image' => '',
            'level' => 'root',
            'container' => true
        );
    }

}

?>
