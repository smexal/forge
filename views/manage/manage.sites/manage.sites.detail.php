<?php
class ManageSitesDetail extends AbstractView {
  public $parent = 'sites';
  public $name = 'detail';
  public $permission = 'manage.sites.detail';
  private $site = false;
  public $permissions = array(
  );
  
  public function content($uri=array()) {
    if(! $this->site && is_numeric($uri[0])) {
      $this->site = new Sites($uri[0]);
    }
    
    if(count($uri) == 1) {
      return $this->mainForm();
    }
  }
  
  private function mainForm() {
    return $this->app->render(TEMPLATE_DIR."views/sites/", "all-form", array(
        'title' => sprintf(i('Manage %1$s'), $this->site->get('name'))
    ));    
  } 
  
}