<?php

class ManageAddNavigationItem extends AbstractView {
    public $parent = 'navigation';
    public $permission = 'manage.navigations.add';
    public $name = 'add-item';
    public $message = '';
    private $navigation = null;
    public $events = array(
        'onAddNavigationItem'
    );

    public function content($uri=array()) {
        $this->navigation = $uri[0];
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => i('Add item to navigation'),
            'message' => $this->message,
            'form' => $this->form()
        ));
    }

    public function onAddNavigationItem($data) {
        $item = explode("##", $data['item']);
        $item_id = $item[1];
        if(array_key_exists('add-to-url', $data)) {
            $item_id.'/'.$data['add-to-url'];
        }
        $this->message = ContentNavigation::addItem($data['navigation'], array(
            "name" => $data["new_name"],
            "parent" => $data['parent'],
            "item" => $item_id,
            "item_type" => $item[0]
        ));
        App::instance()->addMessage(sprintf(i('Navigation Item %1$s has been added.'), $data['new_name']), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'navigation')));
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'navigation', 'add-item')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("additional-form-url", Utils::getUrl(array('api', 'edit-navigation-item-additional-form')));
        $form->hidden("event", $this->events[0]);
        $form->hidden("navigation", $this->navigation);
        $form->input("new_name", "new_name", i('Item name'), 'input', '');

        $items = $this->getItems();
        $form->select(array(
            "key" => 'item',
            "label" => i('Select item'),
            "values" => $this->getItems()
        ), '');

        $items = $this->getNavigationItems($this->navigation);
        $items["0"] = i('No Parent');
        asort($items);

        $form->select(array(
            "key" => 'parent',
            "label" => i('Select a parent item'),
            "values" => $items
        ), '');
        $form->submit(i('Add item'));
        return $form->render();
    }

    private function getNavigationItems($navigation) {
        $items = array();
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
