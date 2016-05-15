<?php

class MediaManagent extends AbstractView {
    public $parent = 'manage';
    public $name = 'media';
    public $permission = 'manage.media';
    public $permissions = array(
    );

    public function content($uri=array()) {
      if(count($uri) > 0) {
          return $this->getSubview($uri, $this);
      } else {
          return $this->app->render(CORE_TEMPLATE_DIR."views/sites/", "generic", array(
              'title' => i('Media'),
              'global_actions' => '',
              'content' => $this->ownContent()
          ));
      }
    }

    private function ownContent() {
        return $this->app->render(CORE_TEMPLATE_DIR."views/", "media", array(
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
