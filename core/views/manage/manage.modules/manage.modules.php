<?php

class ModuleManagent extends AbstractView {
    public $parent = 'manage';
    public $name = 'modules';
    public $permission = 'manage.modules';
    public $permissions = array(
        0 => 'manage.modules.add'
    );

    public function content($uri=array()) {
      if(count($uri) > 0) {
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
      foreach($this->app->mm->modules as $module) {
        $errors = array();
        $check = $module->check();
        if($check === true) {
          // save to display this module
          $return.= $this->app->render(CORE_TEMPLATE_DIR."assets/", "grid-block", array(
              
          ));
        } else {
          $errors[] = $check;
        }
      }
      foreach($errors as $e) {
        $return.=$e;
      }
      return $return;
    }
}

?>
