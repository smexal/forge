<?php

class MediaManager {

    public function filterFileTypes($file) {
        // TODO: Filter hazardous file types.
        return $file;
    }

    public function create($file) {
        if(! Auth::allowed("manage.media")) {
            return false;
        }
        $media = new Media();
        $media->create($file);
    }

    public function getAll() {
        $db = App::instance()->db;
        $db->orderBy('date', 'desc');
        $media = $db->get('media');
        $return = array();
        foreach($media as $med) {
            array_push($return, new Media($med['id'], $med));
        }
        return $return;
    }
}

?>
