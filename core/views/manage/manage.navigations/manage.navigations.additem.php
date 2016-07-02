<?php

class ManageAddNavigationItem extends AbstractView {
    public $parent = 'navigation';
    public $permission = 'manage.navigations.add';
    public $name = 'add-item';
    public $message = '';
    public $events = array(
        'onAddNavigationItem'
    );

    public function content($uri=array()) {
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => i('Add item to navigation'),
            'message' => $this->message,
            'form' => $this->form()
        ));
    }

    public function onAddNavigationItem($data) {
        /*$position = '';
        if(array_key_exists('position', $data)) {
            $position = $data['position'];
        }
        $this->message = ContentNavigation::create($data['new_name'], $position);
        App::instance()->addMessage(sprintf(i('Navigation %1$s has been created.'), $data['new_name']), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'navigation')));*/
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'users', 'add')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->input("new_name", "new_name", i('Item name'), 'input', '');

        $items = $this->getItems();
        $form->select(array(
            "key" => 'new_name',
            "label" => i('Select item'),
            "values" => $this->getItems()
        ), '');
        $none = array();
        $none[0] = i('No Parent');
        $form->select(array(
            "key" => 'new_name',
            "label" => i('Select a parent item'),
            "values" => array_merge(
                $none,
                $this->getItems()
            )
        ), '');
        $form->submit(i('Add item'));
        return $form->render();
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
      return $items;
    }
}

?>
