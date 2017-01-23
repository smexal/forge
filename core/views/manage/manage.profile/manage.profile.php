<?php

namespace Forge\Core\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\APIKeys;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Utils;



use function \Forge\Core\Classes\i;

class Profile extends View {
    public $parent = 'manage';
    public $name = 'profile';
    public $permission = 'manage';

    private $subviews = [];

    public function content($uri=array()) {
        $user = App::instance()->user;

        $this->subviews = [
            [
                'title' => i('API Settings', 'core'),
                'url' => 'api-settings',
                'callable' => 'apiSettings'
            ]
        ];

        $subview = false;
        $subviewName = false;
        $subviewActions = false;

        if(array_key_exists('create-key', $_GET)) {
            $this->createApiKey();
            App::instance()->redirect(Utils::getCurrentUrl());
        }

        if(count($uri) > 0) {
            foreach($this->subviews as $s) {
                if($s['url'] == $uri[0]) {
                    $callable = $s['callable'];
                    $subview = $this->$callable();
                    if(method_exists($this, $callable."Actions")) {
                        $actionCallable = $callable."Actions";
                        $subviewActions = $this->$actionCallable();
                    }
                    $subviewName = $s['url'];
                }
            }
        }

        return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "oneform", array(
            'action' => Utils::getCurrentUrl(),
            'event' => "updateUserProfile",
            'title' => sprintf(i('%1$s\'s Profile', 'core'), $user->get('username')),
            'tabs' => false,
            'tab_content' => array(),
            'global_actions' => Fields::button(i('Save changes')),
            'subnavigation' => $this->subviews,
            'subnavigation_root' => Utils::getUrl(["manage", 'profile']),
            'general_name' => i('General', 'core'),
            'subview_name' => $subviewName,
            'subview_actions' => $subviewActions,
            'subview' => $subview
        ));
    }

    private function createApiKey() {
        $apiKeys = new APIKeys();
        $apiKeys->create();
    }

    private function apiSettings() {
        $apiKeys = new APIKeys();
        $keys = $apiKeys->getAll();
        if(count($keys) == 0) {
            return '<p class="alert alert-info">'.i('No API Key\'s for your user created. Create one with the link on the top right.', 'core').'</p>';
        } else {
            return $this->getApiKeyList($keys);
        }
    }

    private function getApiKeyList($keys) {
        $return = '<ul>';
        foreach($keys as $key) {
            $return.= '<li>'.$key['keey'].'</li>';
        }
        $return.= '</ul>';
        return $return;
    }

    private function apiSettingsActions() {
        $keyUrl = Utils::getUrl(
            ['manage', 'profile', 'api-settings'],
            true,
            [
                'create-key' => "true"
            ]
        );
        return '<a class="btn btn-xs" href="'.$keyUrl.'">'.i('Create key', 'forge-events').'</a>';        
    }

}

?>
