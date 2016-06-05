<?php

class CollectionManagementCategories extends AbstractView {
    public $parent = 'collections';
    public $name = 'categories';
    public $permission = 'manage.collections.categories';
    public $events = array(
    );

    public function content($uri=array()) {
        return App::instance()->render(CORE_TEMPLATE_DIR.'views/parts/', 'crud.modify', array(
            'title' => i('Configure Categories', 'core'),
            'message' => false,
            'form' => $this->currentCategories().$this->addNew()
        ));
    }

    private function currentCategories() {
        return 'list of current';
    }

    private function addNew() {
        return 'add a new one';
    }
}

?>
