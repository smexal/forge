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
    const DEBUG_NONE = false;
    const DEBUG_LOG  = true;
    const DEBUG_PAGE = 'page';
    
    public static $DEBUG = false;
    public static $FLUSH = false;

    public static $BASE_DIR = null;
    public static $EXCLUDED_FILES = [];
    public static $HEAD_LINES = 50;

    private static $instance = null;

    private $paths = array();
    private $mappings = array();

    private function __construct() {}

    public static function instance() {
        if(is_null(static::$instance)) {
            static::$instance = new SuperLoader();
            static::$instance->loadClasses();
        }
        return static::$instance;
    }

    protected function loadClasses() {
      $mappings = $this->maybeGetCache(static::$FLUSH);
      if(!is_null($mappings)) {
        Logger::info("Loaded Cached Autoload-Mappings via Cache-File...");
        $this->mappings = $mappings;
        return;
      }

      Logger::info("Recreating Autoload-Mappings via Filesystem...");
      $start = Logger::timer();
      $files = $this->getFilesRecursively(static::$BASE_DIR);
      $this->mappings = $this->getClassMappings($files);
      PickleCache::writeCache(get_called_class(), $this->mappings);
      Logger::stop($start, "ERROR");
    }

    /**
     * Gets the loaded Classes from the cache if they are available
     * 
     * @param boolean $flush Ignores the cache (as if none found)
     */
    protected static function maybeGetCache($flush){
        if($flush)
            return null;

        $mapppings = null;
        $cache_key = get_called_class();
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
                $fileInfo->next();
                continue;
            } else if($fileInfo->isDir()) {
                $files = array_merge($files, $this->getFilesRecursively($fileInfo->getPathname()));
            } else if($fileInfo->isFile() && preg_match($pattern, $fileInfo->getPathname())) {
                $files[] = $fileInfo->getPathname();
            }
        }
        return $files;
    } 

    public function getClassMappings($files) {
      $mappings = [];

      foreach($files as $file) {
        $head = $this->readHead($file, static::$HEAD_LINES);
        if(!preg_match('/namespace\s+(.*)\;/', $head, $ns_match)) {
          error_log("Cant find namespace for $file");
          continue;
        }
        if(!preg_match('/\\n((abstract\s+)?class|interface|trait)\s+([a-zA-Z][a-zA-Z0-9_]+)/', $head, $cls_match)) {
          error_log("Can't find class for file $file");
          continue;
        }
       
        $ns_cls = $ns_match[1] . '\\' . $cls_match[3];
        $mappings[$ns_cls] = $file;
      }
      return $mappings;
    }

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

    public function autoloadClass($ns_cls) {
        if(array_key_exists($ns_cls, $this->mappings)) {
            require_once($this->mappings[$ns_cls]);
            return;
        }
        echo "<pre>";
        var_dump($ns_cls);
        var_dump($this->mappings);
    }
}