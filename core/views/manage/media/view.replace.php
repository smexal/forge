<?php

namespace Forge\Core\Views\Manage\Media;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\Utils;

class ReplaceView extends View {
    public $parent = 'media';
    public $name = 'replace';
    public $permission = 'manage.media';
    public $permissions = array(
    );
    private $media = null;
    public $events = array(
        "onUpdateMedia"
    );

    public function content($uri=array()) {
        $this->media = new Media($uri[0]);
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", "crud.modify", array(
            'title' => sprintf(i('Replace Media `%s`', 'core'), $this->media->title),
            'message' => '',
            'form' => $this->mediaReplace().$this->cancelLink()
        ));
    }

    private function mediaReplace() {
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "media-upload", array(
            'upload_handler_url' => Utils::getUrl(array('api', 'media', 'replace', $this->media->id)),
            'redirect_url' => Utils::getUrl(array('manage', 'media', 'detail', $this->media->id)),
            'inOverlay' => 'true'
        ));
    }

    private function cancelLink() {
        return Utils::overlayButton(Utils::getUrl(['manage', 'media', 'detail', $this->media->id]), i('Cancel', 'core'));
    }
}
