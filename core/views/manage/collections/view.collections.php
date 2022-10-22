<?php

namespace Forge\Core\Views\Manage\Collections;

use Forge\Core\Abstracts\View;
use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\App\ModifyHandler;
use Forge\Core\Classes\User;
use Forge\Core\Classes\Utils;
use Forge\Core\Classes\TableBar;
use Forge\Core\Traits\ApiAdapter;

class CollectionsView extends View {
    use ApiAdapter;

    public $parent = 'manage';
    public $name = 'collections';
    public $permission = 'manage.collections';
    public $permissions = array(
        'add' => "manage.collections.add",
        'configure' => "manage.collections.configure",
        'categories' => "manage.collections.categories",
        'delete' => "manage.collections.delete"
    );

    public $collection = false;
    private $apiMainListener = 'collections-view';

    public function content($uri=array()) {
        // find out which collection we are editing
        foreach (App::instance()->cm->collections as $collection) {
            if ($collection->getPref('name') == $uri[0]) {
                $this->collection = $collection;
                break;
            }
        }

        // check if user has permission
        if ($collection && Auth::allowed($collection->permission)) {
            // render subview
            if (count($uri) > 1) {
                return $this->subviews($uri);
                // render the overview
            } else {
                if(Auth::allowed($this->permissions['add'])) {
                    $global_actions = App::instance()->render(CORE_TEMPLATE_DIR."assets/", "overlay-button", array(
                        'url' => Utils::getUrl(array('manage', 'collections', $this->collection->getPref('name'), 'add')),
                        'label' => $this->collection->getPref('add-label')
                    ));
                } else {
                    $global_actions = '';
                }
                if (Auth::allowed($this->permissions['configure'])) {
                    $global_actions.= App::instance()->render(CORE_TEMPLATE_DIR."assets/", "overlay-button", array(
                        'url' => Utils::getUrl(array('manage', 'collections', $this->collection->getPref('name'), 'configure')),
                        'label' => i('Configure', 'core')
                    ));
                }
                if ($collection->preferences['has_categories'] && Auth::allowed($this->permissions['categories'])) {
                    $global_actions.= App::instance()->render(CORE_TEMPLATE_DIR."assets/", "overlay-button", array(
                      'url' => Utils::getUrl(array('manage', 'collections', $this->collection->getPref('name'), 'categories')),
                      'label' => i('Categories', 'core')
                    ));
                }
                return App::instance()->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
                    'title' => $this->collection->getPref('all-title'),
                    'global_actions' => $global_actions,
                    'content' => $this->collectionList()
                ));
            }
        } else {
            App::instance()->redirect("denied");
        }
    }

    private function subviews($uri) {
        switch ($uri[1]) {
            case 'add':
                return $this->getSubview('add', $this);
            case 'delete':
                return $this->getSubview('delete', $this);
            case 'edit':
                return $this->getSubview('edit', $this);
            case 'categories':
                return $this->getSubview('categories', $this);
            case 'configure':
                return $this->getSubview('configure', $this);
            case 'assign':
                return $this->getSubview('add', $this, $uri[2], $uri[3]);
            default:
               return '';
        }
    }

    private function collectionList() {
        $headings = [
            Utils::tableCell(i('Name')),
        ];

        if($this->collection->preferences['has_categories'] === true) {
            $headings[] = Utils::tableCell(i('Categories'));
        }

        $headings = array_merge($headings, [
            Utils::tableCell(i('Author')),
            Utils::tableCell(i('Created')),
            Utils::tableCell(i('status')),
            Utils::tableCell(i('Actions'))
        ]);

        $headings = ModifyHandler::instance()->trigger('ForgeCore_CollectionManagement_HeaderList', $headings);

        $table = [
            'id' => "collectionTable",
            'th' => $headings,
            'td' => $this->getPageRows()
        ];

        $bar = new TableBar(Utils::url(['api', $this->apiMainListener]), 'collectionTable', '&collection='.$this->collection->name);
        $bar->enableSearch();
        $bar->enableSorting([
            'default' => i('Default', 'core'),
            'name_ASC' => i('Name Ascending', 'core'),
            'name_DESC' => i('Name Descending', 'core')
        ]);
        $bar->addDirectFilter([
            'label' => i('Category', 'core'),
            'field' => 'categories',
            'values' => [
                0 => 'category 1',
                1 => 'category 2'
            ]
        ]);

        return $bar->render() . App::instance()->render(CORE_TEMPLATE_DIR."assets/", "table", $table);
    }

    /**
     * API Search method for table
     * @return json tr's for the table
     */
    public function search() {
        if(! isset($_GET['collection'])) {
            return;
        }
        // find out which collection we are editing
        foreach (App::instance()->cm->collections as $collection) {
            if ($collection->getPref('name') == $_GET['collection']) {
                $this->collection = $collection;
                break;
            }
        }


        $args = ['query' => $_GET['t']];
        if ($_GET['s'] !== 'default' && $_GET['s'] !== '') {
            $sort = explode("_", $_GET['s']);
            $args['order'] = $sort[0];
            $args['order_direction'] = $sort[1];
        }

        foreach ($_GET as $key => $value) {
            if (strstr($key, 'filter__')) {
                $k = explode("__", $key);
                $k = $k[1];
                $args['where'][$k] = $value;
            }
        }

        return json_encode([
            'newTable' => App::instance()->render(
                CORE_TEMPLATE_DIR . 'assets/',
                'table-rows',
                ['td' => $this->getPageRows(0, 0, $args)]
            )
        ]);
    }

    private function getPageRows($parent=0, $level=0, $sort = false, $args = false) {
        $rows = array();
        foreach ($this->collection->items($args) as $item) {
            $user = new User($item->getAuthor());
            $row = new \stdClass();
            $title = $item->getMeta('title');
            if(strlen($title) == 0) {
                $title = $item->getName();
            }
            $title = ModifyHandler::instance()->trigger(
                'modify_collection_listing_title',
                $title,
                $item
            );
            $row->tds = [
                Utils::tableCell(
                    App::instance()->render(CORE_TEMPLATE_DIR."assets/", "a", array(
                        "href" => Utils::getUrl(array("manage", "collections", $this->collection->getPref('name'), 'edit', $item->id)),
                        "name" => $title
                    ))
                )
            ];

            if($this->collection->preferences['has_categories'] === true) {
                    $row->tds[] = Utils::tableCell($this->getCategoriesString($item));
            }

            $row->tds = array_merge($row->tds, [
                Utils::tableCell($user->get('username')),
                Utils::tableCell(Utils::dateFormat($item->getCreationDate())),
                Utils::tableCell(i($item->getMeta('status'))),
                Utils::tableCell($this->actions($item), false, false, false, Utils::url(["manage", "collections", $this->collection->getPref('name'), 'edit', $item->id]))
            ]);

            $row->rowAction = Utils::getUrl(['manage', 'collections', $item->getType(), 'edit', $item->id]);

            array_push($rows, $row);
        }
        return $rows;
    }

    private function getCategoriesString($item) {
        $cats = $item->getMeta('categories');
        $categories = [];
        if(!is_array($cats)) {
            return '';
        }
        foreach($cats as $c) {
            $meta = $this->collection->getCategoryMeta($c);
            $categories[] = $meta->name;
        }
        return implode(", ", $categories);
    }

    private function actions($item) {
        $actions = array(
            array(
                "url" => Utils::getUrl(array("manage", "collections", $this->collection->getPref('name'), 'edit', $item->id)),
                "icon" => "mode_edit",
                "name" => sprintf(i('edit %s'), $this->collection->getPref('single-item')),
                "ajax" => false,
                "confirm" => false
            )
        );
        if (Auth::allowed($this->permissions["delete"])) {
            array_push($actions, array(
                "url" => Utils::getUrl(array("manage", "collections", $this->collection->getPref('name'), 'delete', $item->id)),
                "icon" => "delete_forever",
                "name" => sprintf(i('delete %s'), $this->collection->getPref('single-item')),
                "ajax" => true,
                "confirm" => true
            ));
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
            'actions' => $actions
        ));
    }
}
