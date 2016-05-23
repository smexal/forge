<?php

class ComponentRow extends Component {
    public $settings = array();

    public function prefs() {
        $this->settings = array(
            array(
                "label" => i("Choose the row layout"),
                "key" => "row-format",
                "type" => "select",
                "values" => array(
                    "12" => "1/1",
                    "6,6" => "1/2 + 1/2",
                    "4,8" => "1/3 + 2/3",
                    "8,4" => "2/3 + 1/3",
                    "4,4,4" => "1/3 + 1/3 + 1/3"
                )
            ),
            array(
                "label" => i("Custom format"),
                "hint" => i("Type a custom format like '4,4,4' always resulting in 12 columns"),
                "key" => "row-format-custom",
                "type" => "text"
            )
        );
        return array(
            'name' => i('Row'),
            'description' => i('Add a row, in which you are able to place other elements.'),
            'id' => 'row',
            'image' => '',
            'level' => 'root',
            'container' => true
        );
    }

    public function content() {
        $prefs = $this->getSavedPrefs();
        $no = 0;
        if(array_key_exists('row-format-custom', $prefs) && strlen($prefs['row-format-custom']) > 1) {
            $columns = $prefs['row-format-custom'];
        } else if(array_key_exists('row-format', $prefs)) {
            $columns = $prefs['row-format'];
        } else {
            $columns = 12;
        }
        $columns = explode(",", $columns);
        $rows = array();
        foreach($columns as $column) {
            array_push($rows, array(
                'width' => $column,
                'content' => $this->getChildrenContent($no)
            ));
            $no++;
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."components/", "row", array(
            'rows' => $rows
        ));
    }

    public function getBuilderContent() {
        $prefs = $this->getSavedPrefs();
        $no = 0;
        if(array_key_exists('row-format-custom', $prefs) && strlen($prefs['row-format-custom']) > 1) {
            $columns = $prefs['row-format-custom'];
        } else if(array_key_exists('row-format', $prefs)) {
            $columns = $prefs['row-format'];
        } else {
            $columns = 12;
        }
        $columns = explode(",", $columns);
        $rows = array();
        foreach($columns as $column) {
            array_push($rows, array(
                'width' => $column,
                'content' => $this->getChildrenBuilderContent($no),
                'add' => Utils::getUrl(array(
                    'manage',
                    'pages',
                    'edit',
                    $this->getPage(),
                    'add-element'
                ), true, array('target' => $this->getId(), 'inner' => $no))
            ));
            $no++;
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."components/builder/", "row", array(
            'rows' => $rows
        ));
    }

}

?>
