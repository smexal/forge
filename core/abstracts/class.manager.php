<?php

namespace Forge\Core\Abstracts;

use \Forge\Core\Interfaces\IManager;
use \Forge\Core\Classes\Cache\Cache;

/**
 * Provides the possibility to let modules and themes register Entities like
 * components, views or collections to be registered by the forge core
 */
abstract class Manager implements IManager {
    protected static $class_dirs = [];
    protected static $classes = [];
    protected static $file_pattern;
    protected static $file_pattern_replace = '$1$2';
    protected static $class_suffix = "";

    public function __construct() {
        static::$classes[get_called_class()] = null;
    }

    /**
     * Adds a class_dir directory which is then loaded by loadClasses()
     * 
     * The directories, files and classes has to be PSR-4 conform. Although it is possible
     * to define a custom Class-Suffix in the inheriting Manager Class via $file_pattern.
     * Additionally it is possible to define a regex-replacement rules for the file-names by defining
     * $file_pattern and $file_pattern_Replace 
     * 
     * @param String $namespace The Namespace prefix for the classes inside the $dir
     * @param String $dir The absolute directory which contains the classes
     */
    public static function addClassDirectory($namespace, $dir) {
        if(!is_dir($dir)) {
            throw new Error("Could not find directory $dir for class managing");
        }
        
        $namespace .= !preg_match('/^.*\\\\$/', $namespace) ? '\\' : '';

        $cc_key = get_called_class();
        if(!array_key_exists($cc_key, static::$class_dirs)) {
            static::$class_dirs[$cc_key] = [];
        }
        static::$class_dirs[$cc_key][] = [$namespace, $dir];
    }

    /**
     * Gets the loaded Classes from the cache if they are available
     * 
     * @param boolean $flush Ignores the cache (as if none found)
     */
    protected static function maybeGetCache($flush){
        if($flush)
            return null;

        if(is_array(static::$classes[get_called_class()]))
            return static::$classes[get_called_class()];

        $classes = null;
        $cache_key = get_called_class();
        if(Cache::cacheExists($cache_key)) {
            try {
                $classes = unserialize(Cache::readCache($cache_key));
            } catch (Exception $e) {
                Logger::info("Could read Manager-Cache for $cache_key");
                $classes = null;
            }
        }
        return $classes;
    }

    protected static function writeCache($classes) {
        $cache_key = get_called_class();
        try {
            Cache::writeCache($cache_key, serialize($classes));
        } catch (Exception $e) {
            Logger::info("Could write Manager-Cache for $cache_key");
        }
    }

    /**
     * Loads the classes based on the defined static::$class_dirs
     * 
     * If the classes are available inside the cache they are returned from it.
     * 
     * @param boolean $flush If the cache shall be flushed. If true, this ignores saved cache values
     */
    protected static function loadClasses($flush) {
        $cc_key = get_called_class();
        static::$classes[$cc_key] = static::maybeGetCache($flush);
        if(is_array(static::$classes[$cc_key])) {
            return static::$classes[$cc_key];
        }
        
        if(!array_key_exists($cc_key, static::$class_dirs))
            return [];

        $classes = [];
        foreach(static::$class_dirs[$cc_key] as $value) {
            $ns  = $value[0];
            $dir = $value[1];

            $files = static::getFilesRecursively($dir);
            foreach($files as &$file) {
                $file = str_replace($dir, '', $file);
            }
            $classes = array_merge($classes, static::filePathsToClassNs($files, $ns));
        }
        static::$classes[$cc_key] = \triggerModifier('Forge\\ClassManager\\AvailableClasses\\' . $cc_key, $classes);
        static::writeCache(static::$classes[$cc_key]);
        return static::$classes[$cc_key];
    }

    /**
     * Gets all php-Files inside a directory and returns their relative paths
     * 
     * @param String $dir The base directory
     */
    protected static function getFilesRecursively($dir) {
        $files = [];
        $pattern = '/^.*\.php$/';
        $iterator = new \DirectoryIterator($dir);
        foreach($iterator as $fileInfo) {
            if($fileInfo->isDot()) {
                $fileInfo->next();
                continue;
            } else if($fileInfo->isDir()) {
                $files = array_merge($files, static::getFilesRecursively($fileInfo->getPathname()));
            } else if($fileInfo->isFile() && preg_match($pattern, $fileInfo->getPathname())) {
                $files[] = $fileInfo->getPathname();
            }
        }
        return $files;
    } 

    /**
     * Rewrites the relative file paths to namespaces.
     * 
     * @param array<Stirng> $files Array with files
     * @param String $ns Namespace which prefixes the generated namespace
     */
    protected static function filePathsToClassNs($files, $ns) {
        $classes_ns = [];
        foreach($files as $file) {

            // Remove leading backslashes
            $file = preg_replace('/^(\\\\|\\/)/', '', $file);

            $cls_ns = $ns . static::pathToNs($file);

            // uc-first the Packages inside the namespace
            $cls_ns = preg_replace_callback('/\\\\[a-z]/', function($matches) {
                $str = '\\' . strtoupper($matches[0][1]);
                return $str;
            }, $cls_ns);

            $classes_ns[] = $cls_ns . static::$class_suffix;
        }

        return $classes_ns;
    }

    /**
     * Rewrites a singel path to a namespace
     * 
     * Additionally corrects the $path to represent the class and not the file name
     * @param String $path The relative file path
     */
    protected static function pathToNs($path) {
        $path = preg_replace('/\//', '\\', $path);
        $path = preg_replace(static::$file_pattern, static::$file_pattern_replace, $path);
        return $path;
    }

}