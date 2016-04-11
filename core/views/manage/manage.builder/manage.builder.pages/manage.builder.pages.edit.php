<?php
/**
 * View for editing and building pages, content and metadata.
 *
 * @package FORGE
 * @author SMEXAL
 * @version 0.1
 */
class ManagePageEdit extends AbstractView {
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
            return $this->defaultContent();
        }
    }

    private function defaultContent() {
        return $this->app->render(CORE_TEMPLATE_DIR."views/", "builder", array(
            'title' => sprintf(i('Edit `%s`'), $this->page->name) . ' ['.strtoupper($this->lang).']',
            'backurl' => Utils::getUrl(array('manage', 'pages')),
            'backname' => i('back to overview'),
            'panel_left' => $this->leftFields(),
            'panel_right' => $this->rightFields(),
            'saveurl' => Utils::getUrl(array('manage', 'pages', 'edit', $this->page->id, 'save')),
            'savetext' => i('Save Changes', 'core'),
            'pageid' => $this->page->id,
            'lang' => $this->lang
        ));
    }

    // displays the left form fields for the edit mask
    private function leftFields() {
        $fields = $this->pages->fields();
        $return = '';
        foreach($fields as $field) {
            if($field['position'] == 'left') {
                $return.= Fields::build($field, $this->page->getMeta($field['key']), $this->lang);
            }
        }
        return $return;
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

?>
