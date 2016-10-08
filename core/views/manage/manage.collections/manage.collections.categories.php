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
        $this->collection->addCategory(array(
            "name" => $_POST['category_name'],
            "parent" => $_POST['parent_category']
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

    private function categoryItems($parent=0) {
        $categories = $this->collection->getCategories($parent);
        $items = '';
        if($parent > 0) {
            $items.= '<ul>';
        }
        foreach($categories as $category) {
            $meta = $this->collection->getCategoryMeta($category['id']);
            $items.= App::instance()->render(CORE_TEMPLATE_DIR."assets/", 'list-item', array(
                'link' => false,
                'children' => $this->categoryItems($category['id']),
                'value' => $meta->name
            ));
        }
        if($parent > 0) {
            $items.= '</ul>';
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

        $categories = $this->collection->getCategories();
        $cats = array(
            array(
                "value" => 0,
                "text" => i('No Parent')
            )
        );
        foreach($categories as $category) {
            $meta = $this->collection->getCategoryMeta($category['id']);
            array_push($cats, array(
                "value" => $category['id'],
                "text" => $meta->name
            ));
        }

        $form->tags("parent_category", "parent_category", i('Parent Category'), $cats, false, false);
        $form->submit(i('Create'));
        return $form->render();
    }
}

?>
