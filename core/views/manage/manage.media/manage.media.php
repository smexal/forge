<?php

class MediaManagent extends AbstractView {
    public $parent = 'manage';
    public $name = 'media';
    public $permission = 'manage.media';
    private $selection = false;
    public $permissions = array(
    );

    public function content($uri=array()) {
        if(array_key_exists('selection', $_GET) && $_GET['selection'] == 1) {
            $this->selection = true;
        }
        if(count($uri) > 0) {
            return $this->getSubview($uri, $this);
        } else {
            return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
                'title' => i('Media'),
                'global_actions' => $this->getGlobalActions(),
                'content' => $this->ownContent()
            ));
        }
    }

    private function getGlobalActions() {
      $return = '';
      // allowed to add pages?
      if($this->selection) {
        $return.= Utils::overlayButton("selectedImage", i('Save selection', 'core'), $_GET['target']);
      }
      return $return;
    }

    private function ownContent() {
        return $this->app->render(CORE_TEMPLATE_DIR."views/", "media", array(
            'selection' => $this->selection,
            'upload_handler_url' => Utils::getUrl(array('api', 'media', 'upload')),
            'redirect_url' => Utils::getUrl(array('manage', 'media')),
            'media' => $this->getMedia()
        ));
    }

    private function getMedia() {
        $mediamanager = new MediaManager();
        $all = $mediamanager->getAll();
        $media_array = array();
        foreach($all as $media) {
            $image = strstr($media->mime, "image/") ? $media->getUrl() : false;
            array_push($media_array, array(
                'id' => $media->id,
                'detail' => Utils::getUrl(array("manage", "media", "detail", $media->id)),
                'image' => $image,
                'mime' => $media->getSimpleMime(),
                'title' => $media->title
            ));
        }
        return $media_array;
    }
}

?>
