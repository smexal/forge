<?php

namespace Forge\Core\Views;

use Forge\Core\Abstracts as Abstracts;

class ModuleManagent extends Abstracts\View {
    public $parent = 'manage';
    public $name = 'modules';
    public $permission = 'manage.modules';
    public $updateContainer = 'modulesgrid';
    public $permissions = array(
        0 => 'manage.modules.add'
    );

    public function content($uri=array()) {
      if(count($uri) > 0) {
        if($uri[0] == 'activate' || $uri[0] == 'deactivate') {
          if($uri[0] == 'activate')
            $this->app->mm->activate($uri[1]);
          if($uri[0] == 'deactivate')
            $this->app->mm->deactivate($uri[1]);
          $this->app->refresh($this->updateContainer, $this->modules());
        }
      } else {
        return $this->ownContent();
      }
    }

    private function ownContent() {
      return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
          'title' => i('Module Management'),
          'global_actions' => '',
          'content' => $this->modules()
      ));
    }

    private function modules() {
      $return = '';
      $errors = false;
      foreach($this->app->mm->modules as $module) {
        $errors = array();
        $check = $module->check();
        if($check === true) {
          // save to display this module
          $activeModules = $this->app->mm->getActiveModules();
          $return.= $this->app->render(CORE_TEMPLATE_DIR."assets/", "grid-block", array(
              'title' => $module->name,
              'meta' => i('Version: ', 'core').$module->version,
              'text' => $module->description,
              'image' => $module->image,
              'image_alt' => $module->name.' '.i('Module Image', 'core'),
              'button' => in_array($module->id, $activeModules) ?
                Utils::getUrl(array("manage", "modules", "deactivate", $module->id)) : 
                Utils::getUrl(array("manage", "modules", "activate", $module->id)),
              'button_text' => in_array($module->id, $activeModules) ? i('Deactivate', 'core') : i('Activate', 'core'),
              'button_class' => 'ajax',
              'additional_class' => in_array($module->id, $activeModules) ? 'active' : ''
          ));
        } else {
          $errors[] = $check;
        }
      }
      if($errors) {
        foreach($errors as $e) {
          $return.=$e;
        }
      }
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "grid", array(
        "id" => $this->updateContainer,
        "content" => $return
      ));
    }
}

?>
