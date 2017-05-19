<?php

namespace Forge\Core\App;

use \Forge\Core\Abstracts\Manager;
use \Forge\Core\Classes\ContentNavigation;
use \Forge\Core\Classes\Logger;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Traits\ApiAdapter;

class NavigationManager extends Manager {
    use ApiAdapter;

    private $apiMainListener = 'navigation';

    public function getItems($navigationId, $data) {
        if(! Auth::allowed("manage.navigations")) {
            return;
        }
        $navigationId = $navigationId[0];

        $dragItems = $this->getDragItems($navigationId);

        return App::instance()->render(CORE_TEMPLATE_DIR."views/parts/", "dragsort", array(
            'callback' => Utils::getUrl(['api', 'navigation', 'update-order']),
            'items' => $dragItems
        ));
    }

    private function getDragItems($navigationId, $parent = 0, $level = 0) {
        $items = ContentNavigation::getNavigationItems($navigationId, false, $parent);
        $dragItems = [];
        foreach($items as $item) {
            $dragItems[] = [
                'level' => $level,
                'id' => $item['id'],
                'content' => $item['name'].'<span class="actions">'.$this->getNavigationItemActions($item['id']).'</span>'
            ];
            $dragItems = array_merge($dragItems, $this->getDragItems($navigationId, $item['id'], $level+1));
        }
        return $dragItems;
    }

    private function getNavigationItemActions($id) {
        $return = '';
        $return.= Utils::iconAction('mode_edit', 'sidebar', Utils::getUrl(["manage", "navigation", "itemedit", $id]));
        $return.= Utils::iconAction('delete', 'sidebar', Utils::getUrl(["manage", "navigation", "itemdelete", $id]));
        return $return;
    }

    public function updateOrder($not_used, $data) {
        $newOrder = $data['itemset'];
        ContentNavigation::updateOrder($newOrder);
    }
}
