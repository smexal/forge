<?php

class CollectionManagementAdd extends AbstractView {
    public $parent = 'collections';
    public $name = 'add';
    public $permission = 'manage.collections.add';

    private $collection = false;

    public function content($uri=array()) {
      // find out which collection we are editing
      $collectionName = Utils::getUriComponents();
      $collectionName = $collectionName[count($collectionName)-2];
      foreach( $this->app->cm->collections as $collection) {
        if($collection->getPref('name') == $collectionName) {
          $this->collection = $collection;
          break;
        }
      }
      if(! is_object($this->collection)) {
        $this->app->addMessage(sprintf(i('Collection "%1$s" has not been found.'), $uri[0]), "warning");
        return '';
      }

      // check if user has permission for this collection
      if(Auth::allowed($this->collection->permission)) {
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => $this->collection->getPref('add-label'),
            'message' => "",
            'form' => $this->form($uri)
        ));
      } else {
        $this->app->redirect("denied");
      }
    }

    public function form($uri) {
        $form = new Form(Utils::getUrl($uri));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("collection", $this->collection->getPref('name'));
        $form->hidden("event", "onAddCollectionItem");
        $form->input("new_title", "new_title", i('Title'), 'input', "");
        $form->submit(i('Save as new Draft'));
        return $form->render();
    }
}

?>
