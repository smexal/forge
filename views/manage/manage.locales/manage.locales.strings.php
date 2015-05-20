<?php

class StringTranslationManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'string-translation';
    public $permission = 'manage.locales.strings';
    public $permissions = array(
        'manage.locales.strings.update'
    );

    public function content($uri=array()) {
      if(count($uri) > 0) {
        if($uri[0] == 'update') {
          return $this->getSubview($uri, $this);
        }
      } else {
        return $this->ownContent();
      }
    }

    public function ownContent() {
      return $this->app->render(TEMPLATE_DIR."views/", "locales", array(
        'title' => i('String Translations'),
        'add' => i('Update Strings'),
        'add_permission' => Auth::allowed($this->permissions[0]),
        'add_url' => Utils::getUrl(array('manage', 'string-translation', 'update')),
        'table' => $this->stringsTable()
      ));
    }

    public function stringsTable() {
      return 'yes';
    }
}

?>
