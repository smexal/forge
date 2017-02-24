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
        $navigationId = $navigationId[0];
        $items = ContentNavigation::getNavigationItems($navigationId, false, 0, true);
        $dragItems = [];
        foreach($items as $item) {
            $dragItems[] = [
                'level' => '0',
                'id' => $item['id'],
                'content' => $item['name']
            ];
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."views/parts/", "dragsort", array(
            'callback' => Utils::getUrl(['api', 'navigation', 'update-order']),
            'items' => $dragItems
        ));
    }

    public function updateOrder($not_used, $data) {
        $newOrder = $data['itemset'];
        ContentNavigation::updateOrder($newOrder);
    }
}

