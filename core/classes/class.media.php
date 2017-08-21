<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

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

    public function getSizedImage($width, $height) {
        if(is_null($this->name)) {
            return '';
        }
        $parts = pathinfo($this->name);
        $type = $parts['extension'];
        $thumbName = $parts['filename'].'__'.$width.'x'.$height.'.'.$type;

        // if thumb already exists return the existing one.
        if(file_exists(UPLOAD_DIR.$this->rel_path.$thumbName)) {
            return $this->url.$thumbName;
        }

        $original = UPLOAD_DIR.$this->rel_path.$this->name;
        list($w, $h) = getimagesize($original);

        if($type == 'jpeg') $type = 'jpg';
        switch($type){
            case 'bmp': $originalImage = imagecreatefromwbmp($original); break;
            case 'gif': $originalImage = imagecreatefromgif($original); break;
            case 'jpg': $originalImage = imagecreatefromjpeg($original); break;
            case 'png': $originalImage = imagecreatefrompng($original); break;
            default : return $this->getUrl();
        }

        // calculating the part of the image to use for thumbnail
        if($w < $width or $h < $height) {
            return $this->getUrl();
        }
        $ratio = max($width/$w, $height/$h);
        $h = $height / $ratio;
        $x = ($w - $width / $ratio) / 2;
        $w = $width / $ratio;

        $new = imagecreatetruecolor($width, $height);
        // preserve transparency
        if($type == "gif" or $type == "png"){
            imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
            imagealphablending($new, false);
            imagesavealpha($new, true);
        }

        imagecopyresampled($new, $originalImage, 0, 0, $x, 0, $width, $height, $w, $h);

        $dst = UPLOAD_DIR.$this->rel_path.$thumbName;
        switch($type){
            case 'bmp': imagewbmp($new, $dst); break;
            case 'gif': imagegif($new, $dst); break;
            case 'jpg': imagejpeg($new, $dst); break;
            case 'png': imagepng($new, $dst); break;
        }
        return $this->url.$thumbName;
    }

    public function getUrl($abs = false) {
        if($abs) {
            return Utils::getAbsoluteUrlRoot().$this->url.$this->name;
        }
        return $this->url.$this->name;
    }

    public function getAbsolutePath() {
        return $this->abs_path.$this->name;
    }

    public function create($file) {
        $this->title = $file["name"];
        $ext = end((explode(".", $this->title)));
        $this->name = md5(microtime()).".".$ext;
        $this->rel_path = $this->getSubdirectory();
        $this->abs_path = UPLOAD_DIR.$this->getSubdirectory();
            $db = App::instance()->db;
            if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR.$this->rel_path.$this->name)) {
            $this->id = $db->insert('media', array(
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

    public function replace($file) {
        if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR.$this->rel_path.$this->name)) {
            // done
        } else {
            // TODO: Error message?
        }
    }

    public function isImage($_mime=false) {
        $mime = $this->mime;
        if ($_mime) {
            $mime = $_mime;
        }

        return strstr($mime, "image/");
    }

    public static function _isImage($mime) {
        return strstr($mime, "image/");
    }

    public function delete() {
        if(! $this->id) {
            return;
        }
        App::instance()->db->where('id', $this->id);
        App::instance()->db->delete('media');

        return unlink($this->abs_path.$this->name);
    }

    public function getSize($readable=true) {
        $f = $this->abs_path.$this->name;
        if(! file_exists($f)) {
            return 0;
        }
        $size = filesize($f);
        if ($readable) {
            $size = human_filesize($size, 2);
        }

        return $size;
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
