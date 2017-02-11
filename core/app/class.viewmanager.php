<?php

namespace Forge\Core\App;
use \Forge\Core\Abstracts\Manager;
use \Forge\Core\App\Modifier;

class ViewManager extends Manager {
    public $views = null;

    protected static $file_pattern = '/(.*)view\.([a-zA-Z][a-zA-Z0-9]*)\.php$/';
    protected static $class_suffix = 'View';

    public function __construct() {
        $this->views = $this->getViews();
    }


    public function getViewByName($name) {
        foreach ($this->views as $view) {
            $v = $view::instance();
            if ($v->name == $name) {
                return $v;
            }
        }
        return;
    }

    public function getViews() {
        App::instance()->eh->fire("onGetViews");
        $flush_cache = ModifyHandler::instance()->trigger('Forge\ViewManager\FlushCache', MANAGER_CACHE_FLUSH === true);
        return  static::loadClasses($flush_cache);
    }

    public function getNavigationViews() {
        $navViews = array();
        foreach ($this->views as $view) {
            $v = $view::instance();
            if ($v->allowNavigation) {
                array_push($navViews, $v);
            }
        }
        return $navViews;
    }
}


