<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

class Page {
    public $id, $parent, $sequence, $name, $modified, $created, $creator, $url, $status, $meta;
    private $db, $eh;

    public function __construct($id) {
        $this->db = App::instance()->db;
        $this->db->where('id', $id);
        $page = $this->db->getOne('pages');
        if ($page) {
            $this->id = $page['id'];
            $this->parent = $page['parent'];
            $this->sequence = $page['sequence'];
            $this->name = $page['name'];
            $this->modified = $page['modified'];
            $this->created = $page['created'];
            $this->url = $page['url'];

            $this->db->where('page', $this->id);
            $this->meta = $this->db->get('page_meta');

            $this->eh = App::instance()->eh;
        } else {
            return;
        }
    }

    public function status($lang = false) {
        if (!$lang) {
            $lang = Localization::getCurrentLanguage();
        }
        $status = $this->getMeta('status', $lang);
        if ($status) {
            return $status;
        } else {
            return 'draft';
        }
    }

    public function getMeta($key, $lang = false) {
        if (!$lang) {
            $lang = Localization::getCurrentLanguage();
        }
        foreach ($this->meta as $meta) {
            if ($meta['keyy'] == $key && ($meta['lang'] == $lang || $meta['lang'] === "0")) {
                return $meta['value'];
            }
        }
        return false;
    }

    public function getUrl() {
        return Utils::getLanguageUrl($this->getUrlParts());
    }

    public function getTitle() {
        return $this->getMeta('title') == '' ? $this->name : $this->getMeta('title');
    }

    public function getUrlParts() {
        $p = $this;
        $parts = [];
        $parts[] = $p->getUrlPart($p);
        while ($p->parent != 0) {
            $p = new Page($p->parent);
            $parts[] = $p->getUrlPart($p);
        }
        $parts = array_reverse($parts);
        return $parts;
    }

    public function getUrlPart($p = null) {
        if (is_null($p)) {
            $p = $this;
        }
        $slug = $p->getMeta('slug');

        if ($slug) {
            $part = $slug;
        } else {
            // normalize "name"
            $part = Utils::slugify($p->name);
        }
        return $part;
    }

    /**
     * Returns the author of a page as user object.
     *
     * @return User Object
     */
    public function author() {
        return new User($this->creator);
    }

    public function lastModified() {
        $this->db->where('id', $this->id);
        $data = $this->db->getOne('pages');
        if (is_null($data['modified'])) {
            return Utils::dateFormat($data['created'], true);
        } else {
            return Utils::dateFormat($data['modified'], true);
        }
    }

    public function setMeta($key, $value, $language) {
        $this->db->where('keyy', $key);
        $this->db->where('page', $this->id);
        $this->db->where('lang', $language);
        $this->db->update('page_meta', array(
            'value' => $value
        ));
    }

    public function insertMeta($key, $value, $language) {
        if (strlen($value) == 0) {
            return;
            // don't save if we don't have anything to save...
        }
        $this->db->insert('page_meta', array(
            'keyy' => $key,
            'lang' => $language,
            'page' => $this->id,
            'value' => $value
        ));
    }

    public function deleteMeta($key, $language) {
        $this->db->where('keyy', $key);
        $this->db->where('lang', $language);
        $this->db->where('page', $this->id);
        $this->db->delete('page_meta');
    }

    public function updateMeta($key, $value, $language) {
        $current_value = $this->getMeta($key, $language);
        if (strlen($value) == 0) {
            // remove meta value, if there is no value
            $this->deleteMeta($key, $language);
        }
        if ($current_value) {
            // update with new
            $this->setMeta($key, $value, $language);
        } else {
            // insert new value
            $this->insertMeta($key, $value, $language);
        }
    }

    public function isPublished() {
        if ($this->getMeta('status') == 'published') {
            return true;
        }
        return;
    }

    public function addMetaTags() {
        $return = '<meta name="description" content="' . $this->getMeta('description') . '">';
        $return .= '<meta http-equiv="content-language" content="' . Localization::getCurrentLanguage() . '">';
        $return .= '<meta name="generator" content="Forge CMS by smexal.ch / forge-cms.com">';

        // real OG Tags
        $return .= '<meta property="og:title" content="' . App::instance()->tm->theme->getTitle() . '" />';
        $return .= '<meta property="og:description" content="' . $this->getMeta('description') . '" />';
        $mediaId = $this->getMeta('mainimage');
        if (is_numeric($mediaId)) {
            $media = new Media($mediaId);
            $return .= '<meta property="og:image" content="' . $media->getUrl(true) . '" />';
        }
        return $return;
    }

    private function getSubnavigationItems() {
        // get all children from root parent
        $root = $this->rootUp($this->id);
        return $this->children(new Page($root));
    }

    private function children($page) {
        $db = App::instance()->db;
        $db->where('parent', $page->id);
        $db->orderBy("sequence", "asc");
        $ps = $db->get('pages');
        $return = '';

        foreach ($ps as $p) {
            $page = new Page($p['id']);
            if (!$page->isPublished()) {
                continue;
            }

            $return .= App::instance()->render(CORE_TEMPLATE_DIR . "assets/", "list-item", [
                'link' => [
                    'active' => count(array_intersect($page->getUrlParts(), Utils::getUriComponents())) == count($page->getUrlParts()) ? true : false,
                    'url' => $page->getUrl()
                ],
                'value' => $page->getMeta('title') == '' ? $page->name : $page->getMeta('title'),
                'children' => $this->children($page)
            ]);
        }

        return $return;
    }

    private function rootUp($id) {
        $p = new Page($id);
        if ($p->parent != 0) {
            return $p->rootUp($p->parent);
        } else {
            return $p->id;
        }
    }

    public function render() {
        $app = App::instance();
        $this->eh->register('onLoadHeader', array($this, 'addMetaTags'));

        // run theme methods..
        if ($app->tm->theme !== '') {
            $app->tm->theme->styles();
        }

        $head = '';
        if ($app->tm->theme !== '') {
            $head = $app->tm->theme->header();
        }

        $globals = array();
        if ($app->tm->theme !== '') {
            $globals = $app->tm->theme->globals();
        }

        $bodyclasses = array();
        $bodyclass = '';
        if ($this->getMeta('movebelownavigation')) {
            array_push($bodyclasses, "no-padding");
        }
        $bodyclass = implode(" ", $bodyclasses);
        if ($this->isPublished()) {
            return $app->render($app->tm->getTemplateDirectory(), "layout", array_merge(
                array(
                    'bodyclass' => $bodyclass,
                    'head' => $head,
                    'body' => $this->content(),
                    'messages' => App::instance()->displayMessages()
                ),
                $globals
            ));
        }
        return i('Access Denied');
    }

    public function content() {
        $content = '';

        // show subnavigation

        if ($this->getMeta('subnavigation') === false) {
            $items = $this->getSubnavigationItems();
            if (strlen($items) == 0) {
                $items = false;
            }
            $content .= App::instance()->render(CORE_TEMPLATE_DIR . "assets/", "subnavigation", array(
                'items' => $items
            ));
        }


        $builder = new Builder('page', $this->id, 'none');
        $elements = $builder->getBuilderElements(Localization::getCurrentLanguage());

        foreach ($elements as $element) {
            $content .= $element->content();
        }
        return $content;
    }
}
