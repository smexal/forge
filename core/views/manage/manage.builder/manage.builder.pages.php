<?php

class PageBuilderManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'pages';
    public $permission = 'manage.builder.pages';
    public $permissions = array(
      0 => 'manage.builder.pages.delete',
      1 => 'manage.builder.pages.add',
      2 => 'manage.builder.pages.edit'
    );

    public function content($uri=array()) {
      if(count($uri) == 0) {
        return $this->defaultContent();
      }
      if(count($uri) > 0 ) {
        switch ($uri[0]) {
          case 'add':
            if(Auth::allowed($this->permissions[0])) {
              return $this->getSubview($uri, $this);
            }
            break;
          case 'delete':
            if(Auth::allowed($this->permissions[1])) {
              return $this->getSubview($uri, $this);
            }
            break;
          case 'edit':
            if(Auth::allowed($this->permission[2])) {
              return $this->getSubview($uri, $this);
            }
          case 'edit-element':
              if(Auth::allowed($this->permission[2])) {
                return $this->getSubview($uri, $this);
              }
          case 'remove-element':
              if(Auth::allowed($this->permission[2])) {
                return $this->getSubview($uri, $this);
              }
          default:
            break;
        }
      }
    }

    private function defaultContent() {
      return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
          'title' => i('Pages'),
          'content' => $this->pageTable(),
          'global_actions' => $this->getGlobalActions()
      ));
    }

    private function getGlobalActions() {
      $return = '';
      // allowed to add pages?
      if(Auth::allowed($this->permissions[1])) {
        $return.= Utils::overlayButton(Utils::getUrl(array("manage" , "pages", "add")), i('Add new page', 'core'));
      }
      return $return;
    }

    private function pageTable() {
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
          'id' => "pagesTable",
          'th' => array(
              Utils::tableCell(i('Name')),
              Utils::tableCell(i('Author')),
              Utils::tableCell(i('last modified')),
              Utils::tableCell(i('status')),
              Utils::tableCell(i('Actions'))
          ),
          'td' => $this->getPageRows()
      ));
    }

    private function getPageRows($parent=0, $level=0) {
      $rows = array();
      $pages = new Pages();
      $items = '';
      $indent = '';
      for($x=0;$x<$level;$x++) {
        $indent.='&nbsp;&nbsp;';
      }
      if($level > 0) {
        $indent.="&minus;&nbsp;";
      }
      foreach($pages->get($parent) as $p) {
        $page = new Page($p['id']);
        $link = $this->app->render(CORE_TEMPLATE_DIR."assets/", "a", array(
            "href" => Utils::getUrl(array("manage", "pages", "edit", $page->id)),
            "name" => $page->name
        ));
        /* Help the translation scanner
         * i('draft')
         * i('published')
         */

        $author = $page->author();
        array_push($rows, array(
          Utils::tableCell($indent.$link),
          Utils::tableCell($author->get('username')),
          Utils::tableCell(Utils::dateFormat($page->modified)),
          Utils::tableCell(i($page->status())),
          Utils::tableCell($this->actions($page->id))
        ));
        $rows = array_merge($rows, $this->getPageRows($page->id, $level+1));
      }
      return $rows;
    }

    private function actions($id) {
      $actions = array(
        array(
            "url" => Utils::getUrl(array("manage", "pages", "edit", $id)),
            "icon" => "pencil",
            "name" => i('edit page'),
            "ajax" => false,
            "confirm" => false
        )
      );
      if(Auth::allowed($this->permissions[0])) {
        array_push($actions, array(
            "url" => Utils::getUrl(array("manage", "pages", "delete", $id)),
            "icon" => "remove",
            "name" => i('delete page'),
            "ajax" => true,
            "confirm" => true
        ));
      }
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
          'actions' => $actions
      ));
    }
}

?>
