<?php

namespace Forge\Core\Classes;

use Forge\Core\Classes\Localization;
use Forge\Core\App\App;
use Forge\Core\Classes\Page;

class Search {
    public static function getQuery() {
        $query = '';
        if(isset($_GET['q'])) {
            $query = $_GET['q'];
        }
        return $query;
    }

    public static function getResults() {

        $results = self::getPageResults();

        // order results by priority
        usort($results, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
        $results = array_reverse($results);

        return $results;
    }

    public static function getPageResults() {
        if(strlen(self::getQuery()) == '')
            return [];

        $page_results = [];
        $db = App::instance()->db;


        // check for titles (priority 100)
        $lang = Localization::getCurrentLanguage();
        $db->where('keyy', 'title');
        $db->where('lang', $lang);
        $db->where('value', '%'.self::getQuery().'%', 'like');
        $entries = $db->get('page_meta');
        foreach($entries as $entry) {
            $page_results[$entry['page']] = 100;
        }

        // check for descriptions (priority 50)
        $db->where('keyy', 'description');
        $db->where('lang', $lang);
        $db->where('value', '%'.self::getQuery().'%', 'like');
        $entries = $db->get('page_meta');
        foreach($entries as $entry) {
            if(array_key_exists($entry['page'], $page_results)) {
                $page_results[$entry['page']]+=50;
            } else {
                $page_results[$entry['page']] = 50;
            }
        }


        // check in page elements (priority 10)
        $db->where('lang', $lang);
        $db->where('builderId', 'none');
        $db->where('prefs', '%'.self::getQuery().'%', 'like');
        $entries = $db->get('page_elements');
        foreach($entries as $entry) {
            if(array_key_exists($entry['pageid'], $page_results)) {
                $page_results[$entry['pageid']]+=10;
            } else {
                $page_results[$entry['pageid']]=10;
            }
        }

        // form well for return
        $returnResults = [];
        foreach($page_results as $page => $priority) {
            $p = new Page($page);
            $returnResults[] = [
                'priority' => $priority,
                'id' => $page,
                'title' => $p->getTitle(),
                'description' => $p->getMeta('description'),
                'url' => $p->getUrl(),
                'type' => 'page'
            ];
        }

        return $returnResults;

    }
}


?>