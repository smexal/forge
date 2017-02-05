<?php

namespace Forge\Core\Views\Manage\Locales;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class LocalesView extends View {
    public $parent = 'manage';
    public $name = 'locales';
    public $permission = 'manage.locales';
    public $permissions = array(
        'manage.locales.add'
    );

    public function content($uri=array()) {
      if(count($uri) > 0) {
        if($uri[0] == 'set-default') {
          Localization::setDefault($uri[1]);
          $this->app->refresh("languageTable", $this->languageTable());
        }
        if($uri[0] == 'add-language') {
          return $this->getSubview($uri, $this);
        }
      } else {
        return $this->ownContent();
      }
    }

    public function ownContent() {
      return $this->app->render(CORE_TEMPLATE_DIR."views/", "locales", array(
        'title' => i('Language Configuration'),
        'add' => i('Add language'),
        'add_permission' => Auth::allowed($this->permissions[0]),
        'add_url' => Utils::getUrl(array('manage', 'locales', 'add-language')),
        'table' => $this->languageTable()
      ));
    }

    private function languageTable() {
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table", array(
          'id' => "languageTable",
          'th' => array(
              Utils::tableCell(i('Code')),
              Utils::tableCell(i('Name')),
              Utils::tableCell(i('Default'))
          ),
          'td' => $this->getLanguageRows()
      ));
    }

    private function getLanguageRows() {
      $languages = $this->app->db->get('languages');
      $language_prepared = array();
      foreach($languages as $language) {
        array_push($language_prepared, array(
            Utils::tableCell($language['code']),
            Utils::tableCell($language['name']),
            Utils::tableCell($this->setDefaultAction($language['id'], $language['default']))
        ));
      }
      return $language_prepared;
    }

    private function setDefaultAction($id, $isDefault) {
      return $this->app->render(CORE_TEMPLATE_DIR."assets/", "table.actions", array(
          'actions' => array(
              array(
                  "url" => Utils::getUrl(array("manage", "locales", "set-default", $id)),
                  "icon" => $isDefault === 0 ? "unchecked" : "ok",
                  "name" => i('Set Default'),
                  "ajax" => true,
                  "confirm" => false
              )
          )
      ));
    }
}

