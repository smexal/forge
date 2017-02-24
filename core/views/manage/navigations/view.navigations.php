<?php

namespace Forge\Core\Views\Manage\Navigations;

use \Forge\Core\Classes\Placeholder;
use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\ContentNavigation;
use \Forge\Core\Classes\Utils;

class NavigationsView extends View {
    public $parent = 'manage';
    public $name = 'navigation';
    public $permission = 'manage.navigations';
    public $permissions = array(
        0 => 'manage.navigations.add'
    );

    public function content($uri=array()) {
        if(count($uri) > 0 && Auth::allowed($this->permissions[0])) {
            return $this->getSubview($uri, $this);
        } else {
            return $this->ownContent();
        }
    }

    public function ownContent() {
        $global_actions = '';
        if(Auth::allowed($this->permissions[0])) {
            $global_actions = $this->app->render(CORE_TEMPLATE_DIR."assets/", "overlay-button", array(
                'url' => Utils::getUrl(array('manage', 'navigation', 'add')),
                'label' => i('Add Navigation', 'core')
            ));
        }

        return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
            'title' => i('Manage Navigations', 'core'),
            'global_actions' => $global_actions,
            'content' => $this->navigationList()
        ));
    }

    private function navigationList() {
        $return = '';
        $panels = [];
        $first = true;

        foreach(ContentNavigation::getNavigations() as $nav) {
            $placeholder = new Placeholder();
            for ($index=0; $index < ContentNavigation::getNavigationCount($nav['id']); $index++) { 
                $placeholder->addBlock('100%', '30px');
            }
            $plRender = $placeholder->render();

            $default = false;
            if($first) {
                $first = false;
                $default = true;
            }
            $panels[] = [
                'default' => $default,
                'id' => 'tab-panel-'.$nav['id'],
                'dataId' => $nav['id'],
                'title' => $nav['name'].' <small>'.$nav['position'].'</small>',
                'body' => $plRender
            ];
        }
        return $this->app->render(CORE_TEMPLATE_DIR."assets/", "accordion", 
            [
                'id' => 'navigations-accordion',
                'panels' => $panels,
                'ajax' => Utils::getUrl(["api", "navigation", "get-items"])
            ]
        );
    }
}

