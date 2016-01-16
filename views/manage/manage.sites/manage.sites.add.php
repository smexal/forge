<?php

class ManageAddPage extends AbstractView {
    public $parent = 'sites';
    public $permission = 'manage.sites.add';
    public $name = 'add';
    public $message = '';
    public $events = array(
        'onCreateNewPage'
    );

    public function content() {
        return $this->app->render(TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => i('Add new page'),
            'message' => $this->message,
            'form' => $this->form()
        ));
    }

    public function onCreateNewPage($data) {
        $real_name = Sites::create($data['sitename']);
        App::instance()->addMessage(sprintf(i('The site "%1$s" has been created.'), $real_name ), "success");
        App::instance()->redirect(Utils::getUrl(array('manage', 'sites')));
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'users', 'add')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->input("sitename", "sitename", i('Page name'), 'input');
        $form->submit(i('Create'));
        return $form->render();
    }
}

?>
