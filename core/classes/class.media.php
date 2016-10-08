<?php

class Media {
    public $rel_path = null;
    public $abs_path = null;
    public $name = null;
    public $title = null;
    public $mime = null;
    public $id = null;
    public $url = null;
    public $alt = null;

    public function __construct($id=null, $data=array()) {
        if(!is_null($id)) {
            $this->id = $id;
            if(count($data) > 0) {
                $this->init($data);
            } else {
                App::instance()->db->where('id', $this->id);
                $data = App::instance()->db->getOne('media');
                if(!is_null($data)) {
                    $this->init($data);
                }
            }
        }
    }

    public function init($data) {
        if(array_key_exists('name', $data)) {
            $this->name = $data['name'];
        }
        if(array_key_exists('mime', $data)) {
            $this->mime = $data['mime'];
        }
        if(array_key_exists('title', $data)) {
            $this->title = $data['title'];
        }
        if(array_key_exists('path', $data)) {
            $this->rel_path = $data['path'];
            $this->abs_path = UPLOAD_DIR.$data['path'];
            $this->url = UPLOAD_WWW.$data['path'];
        }
    }

    public function getUrl($abs = false) {
        if($abs) {
            return Utils::getAbsoluteUrlRoot().$this->url.$this->name;
        }
        return $this->url.$this->name;
    }

    public function create($file) {
        $failure = false;
        $this->title = $file["name"];
        $ext = end((explode(".", $this->title)));
        $this->name = md5(microtime()).".".$ext;
        $this->rel_path = $this->getSubdirectory();
        $this->abs_path = UPLOAD_DIR.$this->getSubdirectory();
        if(! move_uploaded_file($file['tmp_name'], UPLOAD_DIR.$this->rel_path.$this->name)) {
            $failure = true;
        }
        if(!$failure) {
            $db = App::instance()->db;
            $db->insert('media', array(
                'name' => $this->name,
                'mime'=> $this->getMimeType(),
                'autor' => App::instance()->user->get('id'),
                'path' => $this->rel_path,
                'title' => $this->title
            ));
        } else {
            Logger::error('There was an error, uploading the file: `'.UPLOAD_DIR.$this->rel_path.$this->name.'` with title `'.$this->title.'`');
        }
    }

    public function isImage() {
        if(strstr($this->mime, "image/")) {
            return true;
        } else {
            return false;
        }
    }

    public function delete() {
        if(! $this->id) {
            return;
        }
        App::instance()->db->where('id', $this->id);
        App::instance()->db->delete('media');
        if(unlink($this->abs_path.$this->name)) {
            return true;
        } else {
            return false;
        }

    }

    public function getSize() {
        return human_filesize(filesize($this->abs_path.$this->name), 2);
    }

    public function getMimeType() {
        if(!is_null($this->mime)) {
            return $this->mime;
        }
        if(is_null($this->rel_path) || is_null($this->name)) {
            return false;
        }
        $this->mime = mime_type(UPLOAD_DIR.$this->rel_path.$this->name);
        return $this->mime;
    }

    public function getSimpleMime() {
        $mime = $this->getMimeType();
        $mime = str_replace("/", "-", $mime);
        $mime = str_replace(" ", "-", $mime);
        return $mime;
    }

    public function getSubdirectory() {
        $dir = date('Y')."/".date('m')."/";
        if(!file_exists(UPLOAD_DIR.$dir)) {
            mkdir(UPLOAD_DIR.$dir, 0777, true);
        }
        return $dir;
    }
}

?>
