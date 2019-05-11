<?php

namespace Forge\Core\Views\Manage\Collections;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Traits\ApiAdapter;

class CategoriesView extends View {
    use ApiAdapter;

    public $parent = 'collections';
    public $name = 'categories';
    public $permission = 'manage.collections.categories';
    public $events = array(
        0 => 'onAddNewCollectionCategory'
    );

    private $apiMainListener = 'categories';

    private $formdata = array();

    public function onAddNewCollectionCategory() {
        $manager = App::instance()->cm;
        $this->collection = $manager->getCollection($this->getCollection());
        $this->collection->addCategory(array(
            "name" => $_POST['category_name'],
            "parent" => $_POST['parent_category']
        ));
    }

    /**
     * Used for the API Adapter Call from the drag and drop sorting.
     */
    public function updateOrder($not_used, $data) {
        $collectionName = $_GET['collection'];
        $manager = App::instance()->cm;
        $this->collection = $manager->getCollection($collectionName);
        $this->collection->updateCategoryOrder($data['itemset']);
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
        return '<div class="card move-top space-top"><h3>'.i('Existing Categories', 'core').'</h3>'.$this->app->render(CORE_TEMPLATE_DIR."views/parts/", "dragsort", array(
            'callback' => Utils::getUrl(["api", "categories", "update-order"], true, ['collection' => $this->getCollection()]),
            'items' => $this->categoryItems(),
            'compact' => true
        )).'</div>';
    }

    private function categoryItems($parent=0, $level=0) {
        $categories = $this->collection->getCategories($parent);
        $items = [];
        foreach($categories as $category) {
            $meta = $this->collection->getCategoryMeta($category['id']);
            $items[] = [
                'level' => $level,
                'id' => $category['id'],
                'content' => '<div class="list-content"><div class="element"><strong>'.$meta->name.'</strong></div><div class="element"><i class="material-icons">edit</i></div></div>'
            ];
            $items = array_merge($items, $this->categoryItems($category['id'], $level+1));
        }
        return $items;
    }

    private function addNew() {
        $form = new Form(Utils::getUrl(Utils::getUriComponents()));
        $form->ajax(".content");
        $form->disableAuto();
        $form->subtitle(i('Add new', 'core'));
        $form->hidden('event', $this->events[0]);
        $form->input('category_name', 'category_name', i('Category Name'), 'input', $this->formdata['category_name']);

        $categories = $this->collection->getCategories();
        $cats = [];
        $cats[0] = i('No Parent', 'core');
        foreach($categories as $category) {
            $meta = $this->collection->getCategoryMeta($category['id']);
            $cats[$category['id']] = $meta->name;
        }

        $form->select([
            'key' => 'parent_category',
            'label' => i('Parent Category', 'core'),
            'values' => $cats,
            'chosen' => true
        ], '');
        $form->submit(i('Create'));
        return '<div class="card space-top">'.$form->render().'</div>';
    }
}

