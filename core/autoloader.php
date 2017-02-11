<?php
namespace Forge;

use \Forge\Core;

/**
* This Class is here to provide loader functionalities
*  for various ressource e.g. classes or
*  provide script or style tags
*/
class AutoLoader {
    const DEBUG_NONE = false;
    const DEBUG_LOG  = true;
    const DEBUG_PAGE = 'page';
    public static $DEBUG = false;

    private static $instance;
    private static $paths = array();
    private static $mappings = array();


    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

      /**
       * Add a path to the autoloader.
       * 
       * Use in conjunction with addMapping in order 
       * to load the correct class 
       * E.g. if multiple class.component.php exist
       * 
       * @param type $paths 
       * @return type
       */
      public static function addPaths($paths) {
          static::$paths = array_merge(static::$paths, $paths);
      }


    /** 
     * Define a set of mapping instructions for the autoloader
     * 
     * Use in conjunction with addPaths in order to first determine in which package (core / module or theme) the loaded
     * class is in.
     * 
     * @param AssocArray $mappings 
     * $mappings[IDENTIFIER] => 
     *       NAMESPACE_REGEX_SELECTOR (This is without the class. So Forge\Core\Classes instead of Forge\Core\Classes\User ),
     *       PATH_SELECTOR (based on NAMESPACE_REGEX_SELECTOR. Which part of the NRS is part of the path),
     *       CLASSNAME_REGEX (This is just the class. So User instad of Forge\Core\User)
     *       FILE_RENAMING (based on CLASSNAME_REGEX. For special filenames like trait.singleton.php) 
     */
    public static function addMapping($mappings) {
        static::$mappings = array_merge(static::$mappings, $mappings);
    }

    static function log($data, $indent=0) {
        if(!static::$DEBUG) {
            return;
        }

        $str = is_string($data) ? $data : print_r($data, 1);
        $str = str_repeat("   ", $indent) . $str;
        if(static::$DEBUG === true) {
            error_log($str);
        } else if (static::$DEBUG == "page") {
            echo("<pre>". $str . "</pre>");
        }
    }

    static function getBasePath($ns_cls) {
        $paths = static::$paths;

        static::log("Getting Base Path for:");
        static::log($ns_cls, 1);

        foreach($paths as $key => $set) {
            $regex = $set[0];
            $path = $set[1];
            static::log("----", 1);
            static::log($key, 1);
            static::log($regex, 1);
            static::log($path, 1);
            if(preg_match($regex, $ns_cls)) {
                static::log("    MATCHED");
                return $path;
            }
        }
        return null;
    }

    static function getMapping($ns_cls) {
        $mappings = static::$mappings;

        static::log("Getting Mapping for:");
        static::log($ns_cls, 1);

        foreach($mappings as $key => $set) {
            $regex = $set[0];
            static::log("----", 1);
            static::log($key, 1);
            static::log($regex, 1);
            static::log($set, 1);
            if(preg_match($regex, $ns_cls)) {
                static::log("    MATCHED");
                return $set;
            }
        }
        return null;
    }

    static function loadClass($ns_cls) {
        $mapping = static::$mappings;

        static::log($ns_cls);

        $namespace = preg_replace('/^(.*\\\\)[^\\\\]+$/', '$1', $ns_cls);
        $cls = preg_replace('/^(.*)\\\\([^\\\\]+)$/', '$2', $ns_cls);

        $base_path = static::getBasePath($ns_cls);

        if(!$base_path) {
            return;
        }

        $mapping = static::getMapping($ns_cls);
        if(!$mapping){
            return;
        }

        $dir = preg_replace($mapping[0], $mapping[1], $namespace);

        static::log("Directory:");
        static::log($namespace,1);
        static::log($mapping[0],1);
        static::log($mapping[1],1);
        static::log($dir,1);

        $file = preg_replace($mapping[2], $mapping[3], $cls);

        static::log("File:");
        static::log($cls,1);
        static::log($mapping[2],1);
        static::log($mapping[3],1);
        static::log($file,1);


        $path = strtolower($base_path . $dir . $file);
        $path = str_replace('\\', '/', $path);

        static::log($file);
        static::log($path);
        if(file_exists($path)) {
            require_once($path);
        }
    }
}
spl_autoload_register(__NAMESPACE__ . "\\Autoloader::loadClass");