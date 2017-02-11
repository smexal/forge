<?php


namespace Forge\Core\Views\Manage\Navigations;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\ContentNavigation;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Utils;

class AddItemView extends View {
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

        $items = ContentNavigation::getPossibleItems();
        $form->select(array(
            "key" => 'item',
            "label" => i('Select item'),
            "values" => ContentNavigation::getPossibleItems()
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
}

