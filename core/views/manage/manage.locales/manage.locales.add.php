<?php

class ManageLocalesAdd extends AbstractView {
    public $parent = 'locales';
    public $permission = 'manage.locales.add';
    public $name = 'add-language';
    public $message = '';
    private $new_code = false;
    private $new_name = false;
    public $events = array(
        'onAddNewLanguage'
    );

    public function content() {
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => i('Add new Language'),
            'message' => $this->message,
            'form' => $this->form()
        ));
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'locales', 'add-language')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->input("new_code", "new_code", i('Language Code'), 'input', $this->new_code);
        $form->input("new_name", "new_name", i('Language Name'), 'input', $this->new_name);
        $form->submit(i('Add'));
        return $form->render();
    }

    public function onAddNewLanguage($data) {
        $this->message = Localization::addNewLanguage($data['new_code'], $data['new_name']);
        if($this->message === true) {
          // new user has been created
          App::instance()->addMessage(sprintf(i('New language %1$s (%2$s) has been added.'), $data['new_name'], $data['new_code']), "success");
          App::instance()->redirect(Utils::getUrl(array('manage', 'locales')));
        } else {
          $this->new_code = $data['new_code'];
          $this->new_name = $data['new_name'];
        }
    }

}

?>
