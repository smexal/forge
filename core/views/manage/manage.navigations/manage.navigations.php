<?php

class NavigationManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'navigation';
    public $permission = 'manage.navigations';
    public $permissions = array(
    );

    public function content($uri=array()) {
        if(count($uri) > 0 && Auth::allowed($this->permissions[0])) {
            //return $this->getSubview($uri, $this);
        } else {
            return $this->ownContent();
        }
    }

    public function ownContent() {
        return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
            'title' => i('Manage Navigations', 'core'),
            'global_actions' => '',
            'content' => $this->navigationList()
        ));
    }

    private function navigationList() {
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
          'id' => "navigationsTable",
          'th' => array(
              Utils::tableCell(i('Name')),
              Utils::tableCell(i('Items')),
              Utils::tableCell(i('Position'))
          ),
          'td' => $this->getPageRows()
      ));
    }

    private function getPageRows($parent=0, $level=0) {
      $rows = array();
      return $rows;
    }
}

?>
