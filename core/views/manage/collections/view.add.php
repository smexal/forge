<?php

namespace Forge\Core\Views\Manage\Collections;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Utils;

class AddView extends View {
    public $parent = 'collections';
    public $name = 'add';
    public $permission = 'manage.collections.add';
    public $events = array(
      'onAddCollectionItem'
    );
    public $title = '';

    private $collection = false;

    public function onAddCollectionItem() {
      $cm = App::instance()->cm;
      $this->message = $cm->add(array(
        'type' => $_POST['collection'],
        'name' => $_POST['new_title']
      ));
      if ($this->message) {
        $this->title = $_POST['new_title'];
      } else {
        // new collection has been created
        App::instance()->addMessage(sprintf(i('Collection %s has been created.'), $_POST['new_title']), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'collections', $_POST['collection'])));
      }
    }

    public function content($uri=array()) {
      // find out which collection we are editing
      $collectionName = Utils::getUriComponents();
      $collectionName = $collectionName[count($collectionName)-2];
      foreach( $this->app->cm->collections as $collection) {
        if ($collection->getPref('name') == $collectionName) {
          $this->collection = $collection;
          break;
        }
      }
      if (! is_object($this->collection)) {
        $this->app->addMessage(sprintf(i('Collection "%1$s" has not been found.'), $uri[0]), "warning");
        return '';
      }

      // check if user has permission for this collection
      if (Auth::allowed($this->collection->permission)) {
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => $this->collection->getPref('add-label'),
            'message' => "",
            'form' => $this->form()
        ));
      } else {
        $this->app->redirect("denied");
      }
    }

    public function form() {
        $form = new Form(Utils::getUrl(array("manage", "collections", $this->collection->getPref('name'), 'add')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("collection", $this->collection->getPref('name'));
        $form->hidden("event", $this->events[0]);
        $form->input("new_title", "new_title", i('Title'), 'input', $this->title);
        $form->submit(i('Save as new Draft'));
        return $form->render();
    }
}

