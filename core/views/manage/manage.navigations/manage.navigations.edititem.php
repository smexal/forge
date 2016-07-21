<?php

class ManageEditNavigationItem extends AbstractView {
    public $parent = 'navigation';
    public $permission = 'manage.navigations.add';
    public $name = 'itemedit';
    public $message = '';
    private $navigation = null;
    public $events = array(
        'onEditNavigationItem'
    );

    public function content($uri=array()) {
        $this->item = ContentNavigation::getItem($uri[0]);
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => i('Edit item'),
            'message' => $this->message,
            'form' => $this->form()
        ));
    }

    public function onEditNavigationItem($data) {
        $item = explode("##", $data['item']);
        $item_id = $item[1];
        if(array_key_exists('add-to-url', $data)) {
            $item_id.='/'.$data['add-to-url'];
        }
        $this->message = ContentNavigation::updateItem($data['item_id'], array(
            "name" => $data["new_name"],
            "parent" => $data['parent'],
            "item" => $item_id,
            "item_type" => $item[0]
        ));
        App::instance()->addMessage(sprintf(i('Changes saved.'), $data['new_name']), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'navigation')));
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'navigation', 'itemedit')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("additional-form-url", Utils::getUrl(array('api', 'edit-navigation-item-additional-form')));
        $form->hidden("event", $this->events[0]);
        $form->hidden("item_id", $this->item['id']);
        $form->input("new_name", "new_name", i('Item name'), 'input', $this->item['name']);

        $items = $this->getItems();
        $form->select(array(
            "key" => 'item',
            "label" => i('Select item'),
            "values" => $this->getItems()
        ), $this->item['item_type'].'##'.$this->item['item_id']);

        $items = $this->getNavigationItems($this->navigation);
        $items["0"] = i('No Parent');
        asort($items);

        $form->select(array(
            "key" => 'parent',
            "label" => i('Select a parent item'),
            "values" => $items
        ), $this->item['parent']);
        $form->submit(i('Save changes'));
        return $form->render();
    }

    private function getNavigationItems($navigation) {
        $items = [];
        foreach(ContentNavigation::getNavigationItems($navigation, false, 0, true) as $item) {
            $items[$item['id']] = $item['name'];
        }
        return $items;
    }

    private function getItems() {
      $items = array();
      $db = App::instance()->db;
      foreach($db->get('pages') as $page) {
        $items['page##'.$page['id']] = $page['name'].' ('.i('Page').')';
      }

      foreach($db->get('collections') as $collection) {
        $items[$collection['type'].'##'.$collection['id']] = $collection['name'].' ('.i($collection['type']).')';
      }

      foreach(App::instance()->vm->getNavigationViews() as $view) {
        $items['view##'.$view->name] = i($view->name).' ('.i('Specific view').')';
      }

      return $items;
    }
}

?>
