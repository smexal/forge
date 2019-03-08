<?php

namespace Forge;

require_once('classes/class.utils.php');
require_once('classes/class.logger.php');
require_once('classes/cache/class.cache.php');
require_once('classes/cache/class.picklecache.php');

use \Forge\Core;
use \Forge\Core\Classes\Logger as Logger;
use \Forge\Core\Classes\Cache\PickleCache;



/**
* This Class is here to provide loader functionalities
*  for various ressource e.g. classes or
*  provide script or style tags
*/
class SuperLoader {
    const DEBUG_NONE = 0;
    const DEBUG_LOG  = 1;
    const DEBUG_PAGE = 2;

    const VERBOSITY_0 = 0;
    const VERBOSITY_1 = 1;
    const VERBOSITY_2 = 2;

    public static $VERBOSITY = SuperLoader::VERBOSITY_1;
    
    public static $DEBUG = SuperLoader::DEBUG_NONE;
    public static $FLUSH = false;

    public static $BASE_DIR = null;
    public static $EXCLUDED_FILES = [];
    public static $HEAD_LINES = 50;

    private static $instance = null;

    private $paths = array();
    private $mappings = array();

    private $ignores = array();

    private function __construct() {}

    public static function instance() {
        if(is_null(static::$instance)) {
            static::$instance = new SuperLoader();
            static::$instance->loadClasses();
        }
        return static::$instance;
    }

    /**
     * Loads Classes based on the provided Class directories
     *
     * The class-mappings are automatically cached
     *
     */
    protected function loadClasses() {
      $mappings = $this->maybeGetCache(static::$FLUSH);
      if(!is_null($mappings)) {
        //Logger::info("Loaded Cached Autoload-Mappings via Cache-File...");
        $this->mappings = $mappings;
        return;
      }

      Logger::info("Recreating Autoload-Mappings via Filesystem...");
      $start = Logger::timer();
      $files = $this->getFilesRecursively(static::$BASE_DIR);
      $this->mappings = $this->getClassMappings($files);
      PickleCache::writeCache(get_called_class(), $this->mappings);
      Logger::stop($start, "INFO");
    }

    /**
     * Gets the loaded Classes from the cache if they are available
     *
     * @param boolean $flush Clears the cache
     */
    protected static function maybeGetCache($flush){
        $cache_key = get_called_class();
        if($flush) {
          PickleCache::flushCache($cache_key);
          return null;
        }

        $mapppings = null;
        if(PickleCache::cacheExists($cache_key)) {
            try {
                $mapppings = PickleCache::readCache($cache_key);
            } catch (Exception $e) {
                Logger::info("Could read SuperLoader-Cache for $cache_key");
                $mapppings = null;
            }
        }
        return $mapppings;
    }

    public static function flushCache() {
      PickleCache::flushCache(get_called_class());
    }

    /**
     * Gets all php-Files inside a directory and returns their relative paths
     *
     * @param String $dir The base directory
     */
    protected  function getFilesRecursively($dir) {
        if(file_exists($dir . '/.noautoload'))
          return [];

        if(in_array(basename($dir), static::$EXCLUDED_FILES))
          return [];

        $files = [];
        $pattern = '/^.*\.php$/';
        $iterator = new \DirectoryIterator($dir);
        foreach($iterator as $fileInfo) {
            if($fileInfo->isDot()) {
                continue;
            } else if($fileInfo->isDir()) {
                $files = array_merge($files, $this->getFilesRecursively($fileInfo->getPathname()));
            } else if($fileInfo->isFile() && preg_match($pattern, $fileInfo->getPathname())) {
                $files[] = $fileInfo->getPathname();
            }
        }
        return $files;
    }

    /**
     * Returns the corresponding classes (fully qualified) based on provided filepaths (absolute)
     *
     * It does this by searching the namespaces and class / interface / trait names via regex.
     *
     * @param Array<String> $files Array with absolute php-file-paths
     */
    public function getClassMappings($files) {
      $mappings = [];

      foreach($files as $file) {
        $head = $this->readHead($file, static::$HEAD_LINES);

        // Get Namespace
        if(!preg_match('/namespace\s+(.*)\;/', $head, $ns_match)) {
            if(!strstr($file, '.rtpl.php')) {
                if(SuperLoader::$VERBOSITY >= SuperLoader::VERBOSITY_2) {
                  error_log("Cant find namespace for $file");
                }
            }
          continue;
        }

        // Get Class / Interface / Trait name
        if(!preg_match('/\\n((abstract\s+)?class|interface|trait)\s+([a-zA-Z][a-zA-Z0-9_]+)/', $head, $cls_match)) {
          if(SuperLoader::$VERBOSITY >= SuperLoader::VERBOSITY_2) {
            error_log("Can't find class for file $file");
          }
          continue;
        }

        $ns_cls = $ns_match[1] . '\\' . $cls_match[3];
        $mappings[$ns_cls] = $file;
      }
      return $mappings;
    }

    /**
     * Reads $lines inside of a file and returns the result
     *
     * @param String $file Path to a existing file
     * @param Integer $lines How Many Lines
     */
    public function readHead($file, $lines) {
      $handle = @fopen($file, "r");
      $header = '';
      if($handle) {
          $ctr = 0;
          while ($ctr < $lines && ($buffer = fgets($handle, 4068)) !== false) {
              $header .= $buffer;
              $ctr++;
          }
          fclose($handle);
      }
      return $header;
    }

    public function addIgnore($ignore) {
      $this->ignores[] = $ignore;
    }

    /**
     * Method has to be registered by spl_autoload_register and is used to load an inexistent class
     *
     * @param String $ns_class Class inclusive namespace
     */
    public function autoloadClass($ns_cls) {
        if(in_array($ns_cls, $this->ignores))
          return;

        if(!preg_match('/Forge\.*/', $ns_cls))
          return;

        if(array_key_exists($ns_cls, $this->mappings)) {
            require_once($this->mappings[$ns_cls]);
            return;
        } 
        
        if(SuperLoader::$DEBUG == SuperLoader::DEBUG_NONE) {
          return;
        }
        
        $log_page = SuperLoader::$DEBUG == SuperLoader::DEBUG_PAGE;
        $str = "SuperLoader could not find $ns_cls";
        
        $str .= $log_page ? "<pre>" : '';
        $str .= "SuperLoader has following Mapping:";
        $str .= print_r($this->mappings, 1);
        $str .= $log_page ? "<pre>" : '';
        
        if($log_page) {
          echo $str;
        } else {
          if(SuperLoader::$VERBOSITY >= SuperLoader::VERBOSITY_2) {
            error_log($str);
          }
        }
    }
}
