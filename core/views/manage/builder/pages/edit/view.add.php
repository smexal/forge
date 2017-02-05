<?php

namespace Forge\Core\Views\Manage\Builder\Edit;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Page;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class AddView extends View {
    public $parent = 'edit';
    public $permission = 'manage.builder.pages.edit';
    public $name = 'add-element';
    private $user = null;
    private $page = false;

    public function content($parts = array()) {
        $this->page = new Page($parts[0]);
        $level = 'root';
        if(is_array($_GET) && array_key_exists('inner', $_GET)) {
            $level = 'inner';
        }

        return App::instance()->render(CORE_TEMPLATE_DIR."views/parts/", "builder.addelement", array(
            "title" => i("Add Element", "core"),
            "components" => $this->components($level)
        ));
    }

    private function components($level) {
        $components = array();
        foreach($this->app->com->getComponentsForLevel($level) as $component) {
            array_push($components, array_merge(
                $component->getPrefSet(),
                array(
                    'url' => Utils::getUrl(array('manage', 'pages', 'edit', $this->page->id, "added-element", $component->getPref('id')), true)
                )
            ));
        }
        return $components;
    }
}

