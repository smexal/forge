<?php

class CollectionManagementCategories extends AbstractView {
    public $parent = 'collections';
    public $name = 'categories';
    public $permission = 'manage.collections.categories';
    public $events = array(
        0 => 'onAddNewCollectionCategory'
    );

    private $formdata = array();

    public function onAddNewCollectionCategory() {
        $manager = App::instance()->cm;
        $this->collection = $manager->getCollection($this->getCollection());
        // TODO ALLOW TO CHOOSE PARENT
        $this->collection->addCategory(array(
            "name" => $_POST['category_name'],
            "parent" => 0
        ));
    }

    private function getCollection() {
        $uri = Utils::getUriComponents();
        return $uri[count($uri)-2];
    }

    public function content($uri=array()) {
        $manager = App::instance()->cm;
        $this->collection = $manager->getCollection($this->getCollection());
        if(! array_key_exists('category_name', $this->formdata)) {
            $this->formdata['category_name'] = '';
        }

        return App::instance()->render(CORE_TEMPLATE_DIR.'views/parts/', 'crud.modify', array(
            'title' => i('Configure Categories', 'core'),
            'message' => false,
            'form' => $this->currentCategories().$this->addNew()
        ));
    }

    private function currentCategories() {
        return App::instance()->render(CORE_TEMPLATE_DIR.'assets/', 'list', array(
            'class' => 'categories',
            'items' => $this->categoryItems()
        ));
    }

    private function categoryItems() {
        $categories = $this->collection->getCategories();
        $items = '';
        foreach($categories as $category) {
            $meta = $this->collection->getCategoryMeta($category['id']);
            $items.= App::instance()->render(CORE_TEMPLATE_DIR."assets/", 'list-item', array(
                'link' => false,
                'children' => false,
                'value' => $meta->name
            ));
        }
        return $items;
    }

    private function addNew() {
        $form = new Form(Utils::getUrl(Utils::getUriComponents()));
        $form->ajax(".content");
        $form->disableAuto();
        $form->subtitle(i("Add new category"));
        $form->hidden("event", $this->events[0]);
        $form->input("category_name", "category_name", i('Category Name'), 'input', $this->formdata['category_name']);
        $form->submit(i('Create'));
        return $form->render();
    }
}

?>
