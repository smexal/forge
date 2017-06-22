<?php

namespace Forge\Core\Views\Manage\Profile;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\APIKeys;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Classes\Logger;

class ProfileView extends View {
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
        // is api-key view
        if($subviewName == $this->subviews[0]['url']) {
            if(count($uri) > 1 && $uri[1] == 'edit') {
                return $this->editKeySettings($uri[2]);
            }
            if(count($uri) > 1 && $uri[1] == 'delete') {
                if(count($uri) == 3) {
                    $apiKeys = new APIKeys();
                    $apiKeys->deleteKey($uri[2]);

                    App::instance()->redirect(array("manage", "profile", "api-settings"));
                }
            }
            if(count($uri) > 1 && $uri[1] == 'toggle-permission') {
                if(count($uri) == 4) {
                    $apiKeys = new APIKeys();
                    $apiKeys->toggle($uri[2], $uri[3]);
                    $this->app->refresh("keyPermissionTable", $this->editKeyTable($uri[2]));
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
        return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
            'id' => "keyTable",
            'th' => array(
                Utils::tableCell(i('id')),
                Utils::tableCell(i('Key')),
                Utils::tableCell(i('Date')),
                Utils::tableCell(i('Actions'))
            ),
            'td' => $this->getKeyRows($keys)
        ));
    }

    private function getKeyRows($keys) {
        $keys_enriched = array();
        foreach($keys as $key) {
            array_push($keys_enriched, array(
                Utils::tableCell($key['id']),
                Utils::tableCell($key['keey']),
                Utils::tableCell(Utils::dateFormat($key['date'])),
                Utils::tableCell($this->keyActions($key['id']))
            ));
        }
        return $keys_enriched;
    }

    private function keyActions($id) {
        return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
            'actions' => array(
                array(
                    "url" => Utils::getUrl(array("manage", "profile", "api-settings", "delete", $id)),
                    "icon" => "remove",
                    "name" => i('delete key'),
                    "ajax" => false,
                    "confirm" => false
                ),
                array(
                    "url" => Utils::getUrl(array("manage", "profile", "api-settings", "edit", $id)),
                    "icon" => "pencil",
                    "name" => i('edit permissions'),
                    "ajax" => true,
                    "confirm" => true
                )
            )
        ));
    }

    private function editKeySettings($id) {
        $form = '<p>';
        $form.= i('Here you can see all the permissions you currently have. Select all Permissions you want to grant for this key.');
        $form.= '</p>';
        $table = $this->editKeyTable($id);
        $form.= $table;

        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => sprintf(i('Edit API Key Permissions')),
            'message' => false,
            'form' => $form
        ));
    }

    private function editKeyTable($id) {
        return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
            'id' => "keyPermissionTable",
            'th' => array(
                Utils::tableCell(i('Permission')),
                Utils::tableCell(i('Granted'), "center")
            ),
            'td' => $this->getKeyPermissionRows($id)
        ));
    }

    private function getKeyPermissionRows($id) {
        $permission_row = array();
        $db = App::instance()->db;
        $db->orderBy("name", "ASC");
        foreach($db->get("permissions") as $permission) {
            if(App::instance()->user->allowed($permission['name'])) {
                array_push($permission_row, array(
                    Utils::tableCell($permission['name']),
                    Utils::tableCell($this->keyPermissionActions($id, $permission['id']), "center"),
                ));
            }
        }
        return $permission_row;
    }

    private function keyPermissionActions($id, $permission) {
        if(APIKeys::allowed($id, $permission)) {
            $icon = "ok";
            $label = i('Remove permission');
        } else {
            $icon = "unchecked";
            $label = i('Grant permission');
        }
        return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
            'actions' => array(
                array(
                    "url" => Utils::getUrl(array("manage", "profile", "api-settings", "toggle-permission", $id, $permission)),
                    "icon" => $icon,
                    "name" => $label,
                    "ajax" => true,
                    "confirm" => false
                ),
            )
        ));
    }

    private function apiSettingsActions() {
        $keyUrl = Utils::getUrl(
            ['manage', 'profile', 'api-settings'],
            true,
            [
                'create-key' => "true"
            ]
        );
        return '<a class="btn btn-xs" href="'.$keyUrl.'">'.i('Create key', 'core').'</a>';        
    }

}

