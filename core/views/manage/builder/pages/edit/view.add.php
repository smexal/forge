<?php

namespace Forge\Core\Views\Manage\Builder\Pages\Edit;

use Forge\Core\Classes\Logger;
use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Page;
use \Forge\Core\Classes\Utils;

class AddView extends View {
    public $parent = 'edit';
    public $permission = 'manage.builder.pages.edit';
    public $name = 'add-element';
    private $user = null;
    private $page = false;
    private $itemId = null;
    private $builderId = null;

    public function content($parts = array()) {
        if(array_key_exists('elementId', $_GET)) {
            $this->itemId = $_GET['elementId'];
            $this->builderId = $_GET['builderId'];
        }
        $level = 'inner';

        return App::instance()->render(CORE_TEMPLATE_DIR.'views/parts/', 'builder.addelement', [
            'searchComponentTitle' => i('Search Component', 'core'),
            'title' => i('Add Element', 'core'),
            'components' => $this->components($level)
        ]);
    }

    private function components($level) {
        $components = array();
        foreach($this->app->com->getComponentsForLevel($level) as $component) {
            array_push($components, array_merge(
                $component->getPrefSet(),
                array(
                    'url' => Utils::getUrl(array('manage', 'pages', 'edit', $this->itemId, "added-element", $component->getPref('id')), true)
                )
            ));
        }
        return $components;
    }
}
