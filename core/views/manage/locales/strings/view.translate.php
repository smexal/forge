<?php


namespace Forge\Core\Views\Manage\Locales\Strings;

use \Forge\Core\Abstracts\View;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Utils;

class TranslateView extends View {
    public $parent = 'string-translation';
    public $name = 'translate';
    public $permission = 'manage.locales.strings.translate';
    private $message = '';
    public $events = array(
        'onUpdateTranslation'
    );

    public function content($uri=array()) {
      return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => i('Translate String'),
            'message' => $this->message,
            'form' => $this->form($uri[0])
      ));
    }
    public function onUpdateTranslation($data) {
      foreach($data as $name => $value) {
        if(strstr($name, "lang-")) {
          $langid = explode("-", $name);
          $langid = $langid[1];
          Localization::translate($data['stringid'], $langid, $value);
        }
      }
    }

    private function form($id) {
        $form = new Form(Utils::getUrl(array('manage', 'string-translation', 'translate', $id)));
        $form->ajax(".content");
        $form->hidden("event", "onUpdateTranslation");
        $form->hidden("stringid", $id);
        $string = Localization::getStringById($id);
        $form->area(
            "string-original",
            i('Orignal String'),
            $string['string'],
            i('Do not replace <code>%s</code> or strings like <code>%1$s</code>, these are placeholders and will be filled with actual values.'),
            true);
        foreach(Localization::getLanguages() as $language) {
          $form->area(
              "lang-".$language['id'],
              $language['name'],
              Localization::stringTranslation($string['string'], $string['domain'], $language['code'])
          );
        }
        $form->submit(i('Update Translation'));
        return $form->render();
    }
}

