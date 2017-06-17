<?php

namespace Forge\Core\Views;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\API;
use \Forge\Core\App\App;
use \Forge\Core\App\MediaManager;
use \Forge\Core\Classes\Pages;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\ContentNavigation;
use \Forge\Core\Classes\Localization;

class ApiView extends View {
    public $name = 'api';
    public $permission = null;
    public $standalone = true;

    public function content($query=array()) {

        $key = false;
        if((array_key_exists('format', $_GET) && $_GET['format'] == 'xml') && ! $headerSet) {
            header('Content-Type: text/xml');
            $format = 'xml';
        } else if(!strstr($_SERVER['HTTP_ACCEPT'], 'text/html')) {
            header('Content-Type: application/json');
            $format = 'json';
        }
        if(array_key_exists('key', $_GET)) {
            $key = $_GET['key'];
        }

        $part = array_shift($query);

        /**
          * TODO Move the hardcoded api registrations to the corresponding class and handle
          * everything through the api adapter trait.
          **/
        switch($part) {
            case 'localization':
                return Localization::apiQuery($query, $format, $key);
            break;
            case 'users':
                return $this->users($query);
            break;
            case 'pages':
                return $this->pages($query);
            break;
            case 'media':
                return $this->media($query);
            break;
            case 'navigation-items':
                return ContentNavigation::getPossibleItems();
            break;
            case 'edit-navigation-item-additional-form':
                return $this->additionalNavigationItemForm($query);
            break;
            default:
                $return = API::instance()->run($part, $query);
                if($return) {
                    return $return;
                } else {
                    return json_encode(array("Unknown Object Query" => $part));
                }
        }
    }

    private function additionalNavigationItemForm($query) {
        $v = App::instance()->vm->getViewByName($query[0]);
        return json_encode($v->additionalNavigationForm());
    }

    private function media($query) {
        $mediamanager = new MediaManager();
        if($query[0] == 'upload') {
            $mediamanager->create($_FILES['file']);
        }
        if($query[0] == 'replace') {
            $mediamanager->replace($_FILES['file'], $query[1]);
        }
    }


    private function pages($query) {
        if(count($query) == 0) {
            // no information about a specific page is requred. return all.
            return json_encode(Pages::getAll());
        } else {
            if($query[0] == 'search') {
            return json_encode(Pages::search($query[1]));
        }
        if($query[0] == 'update-order') {
            return Pages::updateOrder($_POST['itemset']);
        }
      }
    }

    private function users($query) {
        if(count($query) == 0) {
            // no information about a specific user is requred. return all.
            return json_encode(User::getAll());
        } else {
            if($query[0] == 'search') {
                return json_encode(User::search($query[1]));
            }
        }
    }
}