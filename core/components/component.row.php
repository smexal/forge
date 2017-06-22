<?php

namespace Forge\Core\Components;

use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\Utils;

class RowComponent extends Component {
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
                    "4,4,4" => "1/3's",
                    "3,3,3,3" => "1/4's"
                )
            ),
            array(
                "label" => i("Custom format"),
                "hint" => i("Type a custom format like `4,4,4` always resulting in 12 columns"),
                "key" => "row-format-custom",
                "type" => "text"
            ),
            array(
                "label" => i("Extra CSS Classes"),
                "hint" => i("Type extra css classes, that you want on this row."),
                "key" => "row-extra-css",
                "type" => "text"
            ),
            array(
                "label" => i("Background Color"),
                "hint" => i("Type a hex or rgba background color."),
                "key" => "row-bg-color",
                "type" => "text"
            ),
            array(
                "label" => i("Adjust Padding"),
                "hint" => i("Adjust padding sizes, you should know, what you\'re doing..."),
                "key" => "row-padding",
                "type" => "text"
            ),
            array(
                "key" => "row-display-type",
                "label" => i('Display Type'),
                "hint" => "Stretch the row to full content or wrap it.",
                "type" => "select",
                "values" => array(
                    "normal" => i('Wrapped row and content'),
                    "semi" => i('Fullwidth row, wrapped content'),
                    "full" => i('Fullwidth row and content')
                )
            ),
            array(
                "label" => i('Background image'),
                "hint" => '',
                "key" => "row-background-image",
                "type" => "image"
            ),
            array(
                'key' => 'background-position',
                'label' => i('Background position'),
                'hint' => '',
                'type' => 'select',
                'values' => array(
                    "cover" => i('Cover'),
                    "center" => i('Center and no repeat')
                )
            ),
            array(
                'key' => 'background-style',
                'label' => i('Background style'),
                'hint' => '',
                'type' => 'select',
                'values' => array(
                    "normal" => i('Normal'),
                    "fixed" => i('Fixed')
                )
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
        if(!array_key_exists('row-display-type', $prefs)) {
            $prefs['row-display-type'] = 'normal';
        }
        if(!array_key_exists('row-extra-css', $prefs)) {
            $prefs['row-extra-css'] = '';
        }
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
        $bg = isset($prefs['row-background-image']) ? $prefs['row-background-image'] : false;
        if(is_numeric($bg)) {
            $bg = new Media($bg);
            $bg = $bg->getUrl();
        }

        return App::instance()->render(CORE_TEMPLATE_DIR."components/", "row", array(
            'rows' => $rows,
            'displaytype' => $prefs['row-display-type'],
            'backgroundimage' => $bg,
            'css' => $prefs['row-extra-css'],
            'bgstyle' => $this->getField('background-style'),
            'bgpos' => $this->getField('background-position'),
            'bgcolor' => $this->getField('row-bg-color'),
            'padding' => $this->getField('row-padding')
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
