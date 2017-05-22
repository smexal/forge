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
        return 'replace...';
    }
}
