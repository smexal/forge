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
            return $page->render();
        } else {
            return 'other';
        }
    }

    private function getStart() {
        if(count($this->parts) == 0) {
            return true;
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
                return true;
            }
        }
        return false;
    }

}


?>
