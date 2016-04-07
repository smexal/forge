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

    public function content($uri=array()) {
        $this->pages = new Pages();
        if(is_numeric($uri[0])) {
            $this->page = new Page($uri[0]);
            return $this->defaultContent();
        }
    }

    private function defaultContent() {
        return $this->app->render(CORE_TEMPLATE_DIR."views/", "builder", array(
            'title' => sprintf(i('Edit `%s`'), $this->page->name),
            'backurl' => Utils::getUrl(array('manage', 'pages')),
            'backname' => i('back to overview'),
            'panel_left' => $this->leftFields(),
            'panel_right' => $this->rightFields()
        ));
    }

    // displays the left form fields for the edit mask
    private function leftFields() {
        $fields = $this->pages->fields();
        foreach($fields as $field) {
            if($field['position'] == 'left') {
                return Fields::build($field);
            }
        }
    }

    // displays the right form fields for the edit mask
    private function rightFields() {
        return 'right';
    }
}

?>
