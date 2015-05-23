<?php

class StringTranslationManagement extends AbstractView {
    public $parent = 'manage';
    public $name = 'string-translation';
    public $permission = 'manage.locales.strings';
    public $languages = null;
    public $permissions = array(
        'manage.locales.strings.update'
    );

    public function init() {
      $this->languages = Localization::getLanguages();
    }

    public function content($uri=array()) {
      if(count($uri) > 0) {
        if($uri[0] == 'update') {
          return $this->getSubview($uri, $this);
        }
        if($uri[0] == 'translate') {
          return $this->getSubview($uri, $this);
        }
      } else {
        return $this->ownContent();
      }
    }

    private function ownContent() {
      return $this->app->render(TEMPLATE_DIR."views/", "locales", array(
        'title' => i('String Translations'),
        'add' => i('Update Strings'),
        'add_permission' => Auth::allowed($this->permissions[0]),
        'add_url' => Utils::getUrl(array('manage', 'string-translation', 'update')),
        'table' => $this->stringsTable()
      ));
    }

    private function stringsTable() {
      return $this->app->render(TEMPLATE_DIR."assets/", "table", array(
          'id' => "string_translations_table",
          'th' => array_merge(array(
              Utils::tableCell(i('String'))
          ), $this->getLanguageNameCells(), array(
              Utils::tableCell(i('In use'), "center"),
              Utils::tableCell(i('Translate'), "center")
          )),
          'td' => $this->getStringRows()
      ));
    }
    
    private function getLanguageNameCells() {
      $cells = array();
      foreach($this->languages as $lang) {
        array_push($cells, Utils::tableCell($lang['name'], "center"));
      }
      return $cells;
    }
    
    private function getStringRows() {
      $rows = array();
      foreach(Localization::getAllStrings() as $string) {
        array_push($rows,
            array_merge(
              array(
                  Utils::tableCell(htmlentities($string['string']))
              ),
              $this->getLanguageTranslationState($string),
              array(
                  $this->stringInUse($string),
                  $this->translateAction($string)
              )
            )
        );
      }
      return $rows;
    }
    
    private function stringInUse($string) {
      return Utils::tableCell(
          $string['used'] == 1 ? Utils::icon("ok-sign") : Utils::icon("question-sign"),
          "center"
      );
    }
    
    private function translateAction($string) {
      return Utils::tableCell($this->app->render(TEMPLATE_DIR."assets/", "table.actions", array(
          'actions' => array(
              array(
                  "url" => Utils::getUrl(array("manage", "string-translation", "translate", $string['id'])),
                  "icon" => "pencil",
                  "name" => i('Translate'),
                  "ajax" => true,
                  "confirm" => true
              )
          )
      )), "center");
    }
    
    private function getLanguageTranslationState($string) {
      $cells = array();
      foreach(Localization::getLanguages() as $language) {
        $translated = Localization::stringTranslation($string['string'], $string['domain'], $language['code']);
        if(! $translated) {
          array_push($cells, Utils::tableCell(Utils::icon("remove"), "center"));
        } else {
          array_push($cells, Utils::tableCell(Utils::icon("ok"), "center"));
        }
      }
      return $cells;
    }
}

?>
