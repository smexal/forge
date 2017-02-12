<?php

namespace Forge\Core\Views\Manage\Navigations;

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
      foreach(ContentNavigation::getNavigations() as $nav) {
        $position = '';
        if($nav['position'] !== '') {
          $position = ' ('.$nav['position'].')';
        }
        $return.= '<div class="navigation-block">';
        $return.= '<h3>'.i('Navigation').': '.$nav['name'].$position.'</h3>';
        $edit_url = Utils::getUrl(array('manage', 'navigation', 'edit', $nav['id']));
        $delete_url = Utils::getUrl(array('manage', 'navigation', 'delete', $nav['id']));
        $return.= '<a href="javascript://" data-open="'.$edit_url.'" class="open-overlay">';
        $return.= i('Edit navigation');
        $return.= '</a> | ';
        $return.= '<a href="javascript://" data-open="'.$delete_url.'" class="open-overlay">';
        $return.= i('Delete navigation');
        $return.= '</a>';
        $return.= $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
            'id' => "navigationsTable",
            'th' => array(
                Utils::tableCell(i('Name')),
                Utils::tableCell(i('Item Type')),
                Utils::tableCell(i('Actions'))
            ),
            'td' => $this->getNavigationItems($nav['id'])
        ));
        $add_url = Utils::getUrl(array('manage', 'navigation', 'add-item', $nav['id']));
        $return.= '<a href="javascript://" data-open="'.$add_url.'" class="open-overlay">';
        $return.= i('Add navigation item');
        $return.= '</a>';
        $return.='</div>';
      }

      return $return;
    }

    private function getNavigationItems($navigation, $parent=0, $level=0) {
        $indent = str_repeat("&nbsp;", $level);
        $level+=10;
        $items = ContentNavigation::getNavigationItems($navigation, false, $parent);
        $item_rows = array();
        foreach($items as $item) {
            array_push($item_rows, array(
                Utils::tableCell($indent.$item['name']),
                Utils::tableCell($item['item_type']),
                Utils::tableCell($this->actions($item['id']))
            ));
            $item_rows = array_merge($item_rows, $this->getNavigationItems($navigation, $item['id'], $level));
        }
        return $item_rows;
    }

    private function actions($id) {
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
            'actions' => array(
                array(
                    "url" => Utils::getUrl(array("manage", "navigation", "itemdelete", $id)),
                    "icon" => "remove",
                    "name" => i('delete item'),
                    "ajax" => true,
                    "confirm" => true
                ),
                array(
                    "url" => Utils::getUrl(array("manage", "navigation", "itemedit", $id)),
                    "icon" => "pencil",
                    "name" => i('edit item'),
                    "ajax" => true,
                    "confirm" => true
                )
            )
        ));
    }

    private function getPageRows($parent=0, $level=0) {
      $rows = array();
      return $rows;
    }
}

