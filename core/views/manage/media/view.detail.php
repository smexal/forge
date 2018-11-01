<?php

namespace Forge\Core\Views\Manage\Media;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\Utils;

class DetailView extends View {
    public $parent = 'media';
    public $name = 'detail';
    public $permission = 'manage.media';
    public $permissions = array(
    );
    private $media = null;
    public $events = array(
        "onUpdateMedia"
    );
    public $message = '';

    public function onUpdateMedia() {
        $this->message = i('Save functionality not yet implemented.');
    }

    public function content($uri=array()) {
        if(is_numeric($uri[0])) {
            $this->media = new Media($uri[0]);

            if(count($uri) > 1 && $uri[1] == 'delete') {
                $deleted = $this->media->delete();
                if(! $deleted) {
                    $this->message = i('There was an error while trying to delete this media.');
                }
                App::instance()->redirect(Utils::getUrl(array('manage', 'media')));
            }

            return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
                'title' => $this->media->title,
                'message' => $this->message,
                'form' => $this->modifyForm()
            ));
        }
    }

    private function modifyForm() {
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "media.detail", array(
            'mime' => $this->media->getMimeType(),
            'size' => $this->media->getSize(),
            'image' => $this->media->isImage() ? $this->media->getUrl() : false,
            'form' => $this->realForm(),
            'url' => $this->media->getUrl()
        ));
    }

    private function realForm() {
        $form = new Form(Utils::getUrl(array('manage', 'media', 'detail', $this->media->id)));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->input("modify_title", "modify_title", i('Title'), 'input', $this->media->title);
        $form->submit(i('Update details'));
        $form = $form->render();
        $form.= Utils::iconAction("find_replace", "ajax", Utils::getUrl(['manage', 'media', 'replace', $this->media->id]));
        $form.= Utils::iconAction("delete_forever", "noOverlay", Utils::getUrl(['manage', 'media', 'detail', $this->media->id, 'delete']));
        return $form;
    }
}
