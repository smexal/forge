<?php

namespace Forge\Core\Views;

use Forge\Core\Abstracts as Abstracts;

/**
 * View for editing and building collections, content and metadata.
 *
 * @package FORGE
 * @author SMEXAL
 * @version 0.1
 */
class ManageCollectionsEdit extends Abstracts\View {
    public $parent = 'collections';
    public $name = 'edit';
    public $permission = 'manage.collections.edit';
    public $permissions = array(
    );

    private $collection = null;
    private $item = null;
    private $lang = null;
    private $uri = null;

    public function content($uri=array()) {
        $this->lang = Localization::currentLang();
        $this->uri = $uri;
        $manager = App::instance()->cm;
        $full_uri = Utils::getUriComponents();
        $this->collection = $manager->getCollection($full_uri[2]);
        $this->item = $this->collection->getItem($this->uri[1]);

        $subview = $full_uri[count($full_uri)-1];
        if($subview == 'save') {
            $this->collection->save($_POST);

            $redir = Utils::getUriComponents();
            array_pop($redir);
            $this->app->redirect($redir);
        }
        if(strlen($subview) > 0 && ! is_numeric($subview)) {
            return $this->defaultContent($subview);
        }
        return $this->defaultContent();
    }

    private function defaultContent($subview = false) {
        if($subview) {
            $subviewContent = $this->collection->getSubview($subview, $this->item->id);
            $subviewActions = $this->collection->getSubviewActions($subview, $this->item->id);
        }

        return $this->app->render(CORE_TEMPLATE_DIR."views/", "builder", array(
            'title' => sprintf(
                i('Edit %1$s %2$s'),
                $this->collection->preferences['single-item'],
                '<span class="highlight">'.$this->item->getName()).'</span>',
            'backurl' => Utils::getUrl(array('manage', 'collections', $this->collection->name)),
            'backname' => i('back to overview'),
            'panel_left' => $this->leftFields(),
            'panel_right' => $this->rightFields(),
            'saveurl' => Utils::getUrl(array_merge(Utils::getUriComponents(), array('save')), true),
            'savetext' => i('Save Changes', 'core'),
            'itemid' => $this->uri[1],
            'lang' => $this->lang,
            'new_url' => false,
            'elements' => false,
            'custom' => $this->collection->customEditContent($this->item->id),
            'general_name' => i('General', 'core'),
            'subview_name' => $subview,
            'subview_actions' => $subview ? $subviewActions : $subview,
            'subview' => $subview ? $subviewContent : $subview,
            'subnavigation_root' => Utils::getUrl(
                array('manage', 'collections', $this->collection->name, 'edit', $this->item->id)
            ),
            'subnavigation' => $this->collection->getSubnavigation()
        ));
    }

    // displays the left form fields for the edit mask
    private function leftFields() {
        $this->collection->addField(array(
            'key' => 'language-switch',
            'label' => i('Change to other language', 'core'),
            'type' => 'linklist',
            'links' => $this->getLanguageLinks(),
            'boxed' => true,
            'order' => 1,
            'position' => 'right',
            'hint' => false
        ));
        $fields = $this->collection->fields();
        $return = '';
        foreach($fields as $field) {
            if($field['position'] == 'left') {
                $return.= Fields::build($field, $this->item->getMeta($field['key']), $this->isMultiLang($field));
            }
        }
        return $return;
    }

    // displays the right form fields for the edit mask
    private function rightFields() {
        $fields = $this->collection->fields();
        $return = '';
        foreach($fields as $field) {
            if($field['position'] == 'right') {
                $return.= Fields::build($field, $this->item->getMeta($field['key'], $this->isMultiLang($field)));
            }
        }
        return $return;
    }

    private function isMultiLang($field) {
        if($field['multilang'] == false) {
            $lang = 0;
        } else {
            $lang = $this->lang;
        }
        return $lang;
    }

    private function getLanguageLinks() {
        $languages = Localization::getActiveLanguages();
        $links = [];
        foreach($languages as $lang) {
            $links[] = array(
                'label' => i($lang['name'], 'core').' '.($lang['code'] == Localization::currentLang() ? i('(Current)') : ''),
                'url' => Utils::getUrl(array("manage", "collections", $this->collection->name ,"edit", $this->item->id)).'?lang='.$lang['code']
            );
        }
        return $links;
    }

}

?>
