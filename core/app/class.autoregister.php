<?php

namespace Forge\Core\App;
/**
 * Automatically registers the types components, views and collections which are defined
 * inside the module / theme and core folders.
 * 
 * The auto registration process is only done if a autoregister.json file is present
 * inside the base directory of the theme / root
 * 
 * Following are the possible configuration-properties for the autoregister.json:
 * TYPE: is either views, components or collections
 * {
 *  "namespace": "\\My\\Namespace", // (required) The base namespace for the module
 *  "nsfromtype": true, // Adds the matching TYPE as a sub-package E.g: \My\Namespace\TYPE
 *  "disabled": [TYPE1, TYPE2], // List of the types which shall not be autoregistered
 *  "TYPE": {
 *      "folder": "comps", // Defines a custom folder instad of the default (TYPE)
 *      "package": "MYPACKAGE" // Defines a custom sub-package for the TYPE
 *  } 
 * }
 * 
 */
class Autoregister {
    static $mapping = [
        'views'       => 'ViewManager',
        'components'  => 'ComponentManager',
        'collections' => 'CollectionManager'
    ];

    static $required = [
        ['namespace']
    ];

    public static function autoregister() {
        $modules = App::instance()->mm->getActiveModules();
        $theme   = DOC_ROOT."themes/" . App::instance()->tm->active.'/';

        $modules = array_map(function($val) {
            return DOC_ROOT."modules/" . $val .'/';
        }, $modules);
        $folders = array_merge(
            [DOC_ROOT, CORE_ROOT, $theme], 
            $modules
        );
        foreach($folders as $folder) {
            static::autoRegisterFolders($folder);
        }
    }

    public static function autoregisterFolders($dir) {
        if(!file_exists($dir .'/autoregister.json'))
            return;
        
        $config = static::loadRegistration($dir . '/autoregister.json');
        $ns = $config['namespace'];
        $ignore = static::_gis([], $config, 'ignore');
        $nsfromtype = static::_gis(false, $config, 'nsfromtype');
        foreach(static::$mapping as $type => $manager) {
            if(in_array($type, $ignore)) {
                continue;
            }
            $sub_dir = static::_gis($type, $config, $type, 'folder');
            $path = $dir . $sub_dir;
            if(!file_exists($path))
                continue;

            $package = $nsfromtype ? '\\' . ucfirst($type) : '';
            $sub_ns =  static::_gis($package, $config, $type, 'package');
            $type_ns = $ns . $sub_ns;

            call_user_func_array('\\Forge\\Core\\App\\' . $manager . '::addClassDirectory', [$type_ns, $path]);
        }
    }

    public static function loadRegistration($file) {
        if(!file_exists($file)) {
            return null;
        }

        $data = file_get_contents($file);
        $data = json_decode($data, true);

        static::valdiateRegistration($data);
        return $data;
    }

    public static function valdiateRegistration($data) {
        foreach(static::$required as $set) {
            $params = array_merge([null, $data], $set);
            if(null === call_user_func_array('\Forge\Core\App\Autoregister::_gis', $params)) {
                throw new \Error("Invalid configuration.\n Configuration:" . print_r($data, 1));
            }
        }
        return false;
    }


    public static function _gis($default, $array) {
        $path = func_get_args();
        array_shift($path);
        array_shift($path);

        $ref = $array;
        foreach($path as $fragment) {
            if(!isset($ref[$fragment]))
                return $default;
            $ref = $ref[$fragment];
        }
        return $ref;
    }
}
