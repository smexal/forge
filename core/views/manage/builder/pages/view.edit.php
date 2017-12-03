<?php

namespace Forge\Core\Views\Manage\Builder\Pages;

use \Forge\Core\Abstracts\View;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Page;
use \Forge\Core\Classes\Pages;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Classes\Builder;



/**
 * View for editing and building pages, content and metadata.
 *
 * @package FORGE
 * @author SMEXAL
 * @version 0.1
 */
class EditView extends View {
    public $parent = 'pages';
    public $name = 'edit';
    public $permission = 'manage.builder.pages.edit';
    public $permissions = array(
    );

    private $pages = null;
    private $page = null;
    private $lang = null;

    public function content($uri=array()) {
        $this->lang = Localization::currentLang();
        $this->pages = new Pages();
        if(is_numeric($uri[0])) {
            $this->page = new Page($uri[0]);
            if(count($uri) > 1 && $uri[1] == 'save') {
                // save changes
                Pages::save($_POST);

                // create new page object after update
                $this->page = new Page($this->page->id);

                // update full view
                $this->app->refresh("builder-content", $this->defaultContent());
            }
            if(count($uri) > 1 && $uri[1] == 'add-element') {
                // slidein required
                return $this->getSubview(array("add-element", $this->page->id), $this);
            }

            if(count($uri) > 1 && $uri[1] == 'added-element') {
                // add page element before redirect
                $elementToAdd = $uri[2];
                $lang = $this->lang;
                $parent = 0;
                $position_x = 0;
                if(array_key_exists('target', $_GET)) {
                    $parent = $_GET['target'];
                }
                if(array_key_exists('lang', $_GET)) {
                    $lang = $_GET['lang'];
                }
                if(array_key_exists('inner', $_GET)) {
                    $position_x = $_GET['inner'];
                }
                $this->page->addElement($elementToAdd, $lang, $parent, "end", $position_x);

                return $this->app->redirect(Utils::getUrl(array("manage", "pages", "edit", $this->page->id), true));
            }
            return $this->defaultContent();
        }
    }

    private function defaultContent() {
        $builder = new Builder('page', $this->page->id);

        return $this->app->render(CORE_TEMPLATE_DIR."views/", "builder", array(
            'title' => sprintf(i('Edit %s'), '<span class="highlight">'.$this->page->name.'</span>'),
            'backurl' => Utils::getUrl(array('manage', 'pages')),
            'backname' => i('back to overview'),
            'panel_left' => $this->leftFields(),
            'panel_right' => $this->rightFields(),
            'saveurl' => Utils::getUrl(array('manage', 'pages', 'edit', $this->page->id, 'save'), true),
            'savetext' => i('Save Changes', 'core'),
            'itemid' => $this->page->id,
            'lang' => $this->lang,
            'builder' => $builder->render(),
            'custom' => '',
            'general_name' => false,
            'subview_name' => false,
            'subview_actions' => false,
            'subview' => false,
            'subnavigation_root' => false,
            'subnavigation' => false
        ));
    }

    // displays the left form fields for the edit mask
    private function leftFields() {
        $this->pages->addField(array(
            'key' => 'language-switch',
            'label' => i('Change to other language', 'core'),
            'type' => 'linklist',
            'links' => $this->getLanguageLinks(),
            'boxed' => true,
            'order' => 1,
            'position' => 'right',
            'hint' => false
        ));
        $fields = $this->pages->fields();
        $return = '';
        foreach($fields as $field) {
            if($field['position'] == 'left') {
                $return.= Fields::build($field, $this->page->getMeta($field['key']), $this->lang);
            }
        }
        return $return;
    }

    private function getLanguageLinks() {
        $languages = Localization::getActiveLanguages();
        $links = [];
        foreach($languages as $lang) {
            $links[] = array(
                'label' => i($lang['name'], 'core').' '.($lang['code'] == Localization::currentLang() ? i('(Current)') : ''),
                'url' => Utils::getUrl(array("manage", "pages", "edit", $this->page->id)).'?lang='.$lang['code']
            );
        }
        return $links;
    }

    // displays the right form fields for the edit mask
    private function rightFields() {
        $fields = $this->pages->fields();
        $return = '';
        foreach($fields as $field) {
            if($field['position'] == 'right') {
                $return.= Fields::build($field, $this->page->getMeta($field['key'], $this->lang));
            }
        }
        return $return;
    }
}
