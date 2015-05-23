<?php

class StringTranslationTranslate extends AbstractView {
    public $parent = 'string-translation';
    public $name = 'translate';
    public $permission = 'manage.locales.strings.translate';
    private $message = '';
    public $events = array(
        'onUpdateTranslation'
    );

    public function content($uri=array()) {
      return $this->app->render(TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => i('Translate String'),
            'message' => $this->message,
            'form' => $this->form($uri[0])
      ));
    }

    private function form($id) {
        $form = new Form(Utils::getUrl(array('manage', 'string-translation', 'translate', 'save')));
        $form->ajax(".content");
        $form->hidden("event", $id);
        $string = Localization::getStringById($id);
        foreach(Localization::getLanguages() as $language) {
          $form->area(
              "lang-".$language['id'], 
              $language['name'],
              Localization::stringTranslation($string['string'], $string['domain'], $language['code']),
              i('Do not replace <code>%s</code> or strings like <code>%1$s</code>, these are placeholders and will be filled with actual values.')
          );
        }
        $form->submit(i('Save Translation'));
        return $form->render();
    }
}

?>
