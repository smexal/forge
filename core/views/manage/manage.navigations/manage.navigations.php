<?php

class NavigationManagement extends AbstractView {
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
      foreach(ContentNavigation::getNavigations() as $nav) {
        $return.= '<h3>'.i('Navigation').': '.$nav['name'].'</h3>';
        $return.= $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
            'id' => "navigationsTable",
            'th' => array(
                Utils::tableCell(i('Name')),
                Utils::tableCell(i('Items')),
                Utils::tableCell(i('Position'))
            ),
            'td' => $this->getNavigationItems($nav['id'])
        ));
        $add_url = Utils::getUrl(array('manage', 'navigation', 'add-item', $nav['id']));
        $return.= '<a href="javascript://" data-open="'.$add_url.'" class="open-overlay">';
        $return.= i('Add navigation item');
        $return.= '</a>';
      }

      return $return;
    }

    private function getNavigationItems($navigation, $parent=0, $level=0) {
      return 'td';
    }

    private function getPageRows($parent=0, $level=0) {
      $rows = array();
      return $rows;
    }
}

?>
