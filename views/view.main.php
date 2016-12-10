<?php

class Main extends AbstractView {
    public $name = 'main';
    public $standalone = true;
    public $default = true;
    public $parts = false;

    public function content($parts=array()) {
        $this->parts = $parts;
        // check if language prepend is set.
        if(! $this->languageSet()) {
            $this->redirectPrependLanguage();
        }
        if($this->getStart()) {
            $page = new Page(Settings::get('home_page'));
            App::instance()->page = $page;
            return $page->render();
        } else {
            // check if is collection
            $collections = App::instance()->cm->getCollections();
            $displayCollectionsItem = false;
            foreach($collections as $collection) {
                if($collection->slug() == $this->parts[0]) {
                    $displayCollectionsItem = $collection;
                    break;
                }
            }
            // no collections item is required, check for pages
            if(! $displayCollectionsItem) {
                $page = $this->getPage(0);
                if($page) {
                    return $page;
                }
            } else {
                $item = $displayCollectionsItem->getBySlug($this->parts[1]);
                if(!is_null($item)) {
                    return $item->render();
                } else {
                    return App::instance()->displayView(App::instance()->vm->getViewByName('404'));
                }
            }
            return App::instance()->displayView(App::instance()->vm->getViewByName('404'));
            // return 404 content...
        }
    }

    private function getStart() {
        if(count($this->parts) == 0) {
            return true;
        }
        return false;
    }

    private function getPage($index, $parent=null) {
        $key = $this->parts[$index];
        if(! is_null($parent)) {
            $this->app->db->where('parent', $parent);
        } else {
            $this->app->db->where('parent', 0);
        }
        $pages = $this->app->db->get('pages');
        foreach( $pages as $page ) {
            $page = new Page($page['id']);
            if($page->getUrlPart() == $this->parts[$index]) {
                if(count($this->parts) > $index+1) {
                    return $this->getPage($index+1, $page->id);
                } else {
                    if($page->id == Settings::get('home_page')) {
                        // this is the home page.. redirect to home...
                        $current = Localization::getCurrentLanguage();
                        $this->app->redirect(array($current));
                    }
                    App::instance()->page = $page;
                    return $page->render();
                }
            }
        }
        return false;
    }

    private function redirectPrependLanguage() {
        $current = Localization::getCurrentLanguage();
        $this->app->redirect(array_merge(array($current),  Utils::getUriComponents()));
    }

    private function languageSet() {
        $original_uri = Utils::getUriComponents();
        if(count($original_uri) == 0) {
            return false;
        }
        $langs = Localization::getLanguages();
        foreach($langs as $lang) {
            if($original_uri[0] == $lang['code']) {
                // make sure to set the language, to the set append.
                Localization::setLang($lang['code']);
                return true;
            }
        }
        return false;
    }

}


?>
