<?php

class ManageSites extends AbstractView {
    public $parent = 'manage';
    public $name = 'sites';
    public $permission = 'manage.sites';
    public $permissions = array(
            0 => 'manage.sites.add'
    );

    public function content($uri=array()) {
        if(count($uri) == 0) {
            return $this->mainContent();
        }
        if(count($uri) > 0 && Auth::allowed($this->permissions[0])) {
            return $this->getSubview($uri, $this);
        }
    }

    public function mainContent() {
      return $this->app->render(TEMPLATE_DIR."views/sites/", "main", array(
              'title' => i('Site Management'),
              'add_permission' => Auth::allowed($this->permissions[0]),
              'add_text' => i('Create new Site'),
              'add_url' => Utils::getUrl(array('manage', 'sites', 'add')),
              'table' => $this->siteList()
      ));
    }
    
    private function siteList() {
      return $this->app->render(TEMPLATE_DIR."assets/", "table", array(
          'id' => "pageTable",
          'th' => array(
                Utils::tableCell(i('Name')),
                Utils::tableCell(i('Last Modified')),
                Utils::tableCell(i('Creator'))
          ),
          'td' => $this->getPageRows()
      ));
    }
    
    private function getPageRows($parent=null, $level = 0) {
        if(is_null($parent)) {
            $this->app->db->where("parent", 0);
        } else {
            $this->app->db->where("parent", $parent);
        }
        $this->app->db->orderBy("sequence", "ASC");
        $sites = $this->app->db->get("sites");
        $rows = array();
        foreach($sites as $site) {
          $namewithlink = '<a href="'.Utils::getUrl(array("manage", "sites", "detail", $site['id'])).'">'.$site['name'].'</a>';
          array_push($rows, array(
              Utils::tableCell(str_repeat('&nbsp;&nbsp;', $level).($level > 0 ? "&ndash; " : '').$namewithlink),
              Utils::tableCell($site['modified']),
              Utils::tableCell(Utils::getUsername($site['creator']))
          ));
          $subrows = $this->getPageRows($site['id'], $level+1);
           
          if(is_array($subrows)) {
              $rows = array_merge($rows, $subrows);
          }
        }
        if(count($rows) > 0) {
            return $rows;
        }
        return false;
    }
}

?>
