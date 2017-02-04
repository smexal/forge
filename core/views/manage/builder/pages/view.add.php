<?php

namespace Forge\Core\Views\Manage\Builder;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Utils;
use \Forge\Core\Classes\Pages;
use \Forge\Core\Classes\Logger;

use function \Forge\Core\Classes\i;

class AddView extends View {
    public $parent = 'pages';
    public $permission = 'manage.builder.pages.add';
    public $name = 'add';
    public $message = '';
    public $new_name = false;
    public $events = array(
        'onAddNewPage'
    );

    public function content($uri=array()) {
      return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
          'title' => i('Add new page'),
          'message' => $this->message,
          'form' => $this->form()
      ));
    }

    public function onAddNewPage($data) {
      $parent = 0;
      if($data['parent'])
        $parent = $data['parent'];

      $this->message = Pages::create($data['new_name'], $data['parent']);

      if($this->message) {
          $this->new_name = $data['new_name'];
      } else {
          // new page has been created
          App::instance()->addMessage(sprintf(i('Page `%1$s` has been created.'), $data['new_name']), "success");
          App::instance()->redirect(Utils::getUrl(array('manage', 'pages')));
      }
    }

    public function form() {
        $form = new Form(Utils::getUrl(array('manage', 'pages', 'add')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->input("new_name", "new_name", i('Page Name'), 'input', $this->new_name);
        $form->tags("parent", "parent", i('Define a parent page'), false, array(
            "value" => "id",
            "name" => "name",
            "url" => Utils::getUrl(array("api", "pages"))
        ));
        $form->submit(i('Create'));
        return $form->render();
    }
}

?>
