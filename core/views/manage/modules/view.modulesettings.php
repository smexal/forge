<?php

namespace Forge\Core\Views\Manage\Modules;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Settings;
use \Forge\Core\Classes\Utils;

class ModulesettingsView extends View {
    public $parent = 'manage';
    public $name = 'module-settings';
    public $permission = 'manage.modules';
    private $settingsAmount = 0;
    private $module = null;
    public $events = array(
        'onUpdateModuleSettings'
    );

    public function content($uri=array()) {
        $module = $uri[0];
        $this->module = App::instance()->mm->getModuleObject($module);

        $fields = $this->currentContent();
        $title = sprintf(i('Manage %s', 'core'), '<span>'.$this->module->name.'</span>');
        if($this->settingsAmount == 0 && count($this->module->settingsViews) == 0) {
            return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
                'title' => $title,
                'content' => '<div class="alert alert-danger">'.i('No Settings available for this module.', 'core').'</div>',
                'global_actions' => false
            ));
        }

        $subview = false;
        if(count($uri) > 1) {
            $moduleSubview = $uri[1];
            foreach($this->module->settingsViews as $settingsView) {
                if($settingsView['url'] == $moduleSubview) {
                    $callable = $settingsView['callable'];
                    $subview = $this->module->$callable();
                    break;
                }
            }
        }
        $subviewName = '';
        if($subview) {
            $subviewName = $moduleSubview;
        }
        $subviewActions = '';
        if($subview) {
            $methodWouldBe = $callable.'Actions';
            if(method_exists($this->module, $methodWouldBe)) {
                $subviewActions = $this->module->$methodWouldBe();
            }
        }

        // there are settings available. render the form.
        return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "oneform", array(
            'action' => Utils::getCurrentUrl(),
            'event' => $this->events[0],
            'title' => $title,
            'tabs' => false,
            'tab_content' => $fields,
            'global_actions' => Fields::button(i('Save changes')),
            'subnavigation' => $this->getSubnavigation(),
            'subnavigation_root' => Utils::getUrl(["manage", 'module-settings', $this->module->id]),
            'general_name' => i('General', 'core'),
            'subview_name' => $subviewName,
            'subview_actions' => $subviewActions,
            'subview' => $subview
        ));
    }

    public function getSubnavigation() {
        return $this->module->settingsViews;
    }

    public function onUpdateModuleSettings() {
        $this->settings = Settings::instance();

        foreach($this->settings->fields as $name => $tab) {
            if($_POST['current-module'] == $name) {
                if(array_key_exists("right", $tab)) {
                    foreach($tab['right'] as $key => $ignored) {
                        Settings::set($key, $_POST[$key]);
                    }
                }
                if(array_key_exists("left", $tab)) {
                    foreach($tab['left'] as $key => $ignored) {
                        Settings::set($key, $_POST[$key]);
                    }
                }
            }
        }

        App::instance()->addMessage(sprintf(i('Changes saved')), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'module-settings', $_POST['current-module'])));
    }

    private function currentContent() {
        $this->settings = Settings::instance();
        return [
            [
                'active' => true,
                'id' => '',
                'left' => $this->getFields('left'),
                'right' => $this->getFields('right')
            ]
        ];
    }

    private function getFields($position) {
        $return = '';
        // add the event as hidden field on the "left" side...
        if($position == 'left') {
            $return .= Fields::hidden([
                'name' => 'current-module',
                'value' => $this->module->id
            ]);
        }
        if(array_key_exists($this->module->id, $this->settings->fields)
            && array_key_exists($position, $this->settings->fields[$this->module->id])) {
            foreach($this->settings->fields[$this->module->id][$position] as $customField) {
                $this->settingsAmount++;
                $return.=$customField;
            }
        }
        return $return;
    }
}

