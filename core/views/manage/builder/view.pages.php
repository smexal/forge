<?php

/* Help the translation scanner
* i('draft')
* i('published')
*/
namespace Forge\Core\Views\Manage\Builder;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Page;
use \Forge\Core\Classes\Pages;
use \Forge\Core\Classes\Utils;

class PagesView extends View {
    public $parent = 'manage';
    public $name = 'pages';
    public $permission = 'manage.builder.pages';
    public $permissions = array(
      0 => 'manage.builder.pages.delete',
      1 => 'manage.builder.pages.add',
      2 => 'manage.builder.pages.edit'
    );

    public function content($uri=array()) {
      if (count($uri) == 0) {
        return $this->defaultContent();
      }
      if (count($uri) > 0 ) {
        switch ($uri[0]) {
          case 'add':
            if (Auth::allowed($this->permissions[0])) {
              return $this->getSubview($uri, $this);
            }
            break;
          case 'delete':
            if (Auth::allowed($this->permissions[1])) {
              return $this->getSubview($uri, $this);
            }
            break;
          case 'edit':
            if (Auth::allowed($this->permission[2])) {
              return $this->getSubview($uri, $this);
            }
          case 'edit-element':
              if (Auth::allowed($this->permission[2])) {
                return $this->getSubview($uri, $this);
              }
          case 'remove-element':
              if (Auth::allowed($this->permission[2])) {
                return $this->getSubview($uri, $this);
              }
          default:
            break;
        }
      }
    }

    private function defaultContent() {
      return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
          'title' => i('Page Management', 'core'),
          'content' => $this->draggableList(),
          'global_actions' => $this->getGlobalActions()
      ));
    }

    private function getGlobalActions() {
      $return = '';
      // allowed to add pages?
      if (Auth::allowed($this->permissions[1])) {
        $return.= Utils::overlayButton(Utils::getUrl(array("manage" , "pages", "add")), i('Add new page', 'core'));
      }
      return $return;
    }

    private function draggableList() {
      return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "dragsort", array(
        'callback' => Utils::getUrl(array("api", "pages", "update-order")),
        'items' => $this->getPageItems()
      ));
    }

    private function getPageItems($parent = 0, $level = 0) {
      $return = array();
      $pages = new Pages();
      foreach($pages->get($parent) as $p) {
        $page = new Page($p['id']);
        $return[] = array(
          'level' => $level,
          'id' => $page->id,
          'content' => $this->getPageListContent($page)
        );
        $return = array_merge($return, $this->getPageItems($page->id, $level+1));
      }
      return $return;
    }

    private function getPageListContent($page) {
      return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "listcontent", [
        'elements' => [
          [
            'top' => $page->status(),
            'main' => '<a href="'.Utils::getUrl(array("manage", "pages", "edit", $page->id)).'">'.$page->name.'</a>'
          ],
          [
            'top' => i('Author', 'core'),
            'main' => $page->author()->get('username')
          ],
          [
            'top' => i('Last modified', 'core'),
            'main' => $page->lastModified()
          ],
          [
            'top' => false,
            'main' => $this->actions($page->id)
          ]
        ]
      ]);
    }

    private function actions($id) {
      $actions = array(
        array(
            "url" => Utils::getUrl(array("manage", "pages", "edit", $id)),
            "icon" => "edit",
            "name" => i('edit page'),
            "ajax" => false,
            "confirm" => false
        )
      );
      if (Auth::allowed($this->permissions[0])) {
        array_push($actions, array(
            "url" => Utils::getUrl(array("manage", "pages", "delete", $id)),
            "icon" => "delete",
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
