<?php

namespace Forge;

use \Forge\Core;

/*
    This Class is here to provide loader functionalities
    for various ressource e.g. classes or
    provide script or style tags
*/
class AutoLoader {
    private static $instance;

    static public function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    static function getConfig() {
      static $config;
      if($config === null)
        $config = unserialize(AUTOLOAD_CONFIG);
      return $config;
    }

    static function getBasePath($ns_cls) {
      $paths = static::getConfig()['paths'];
      foreach($paths as $regex => $path) {
        if(preg_match($regex, $ns_cls))
          return $path;
      }
      return null;
    }

    static function loadClass($ns_cls) {
      $mapping = static::getConfig()['mapping'];

      $ns_parts = explode('\\', $ns_cls);
      $cls = $ns_parts[count($ns_parts) - 1];
      unset($ns_parts[count($ns_parts) - 1]);

      $ns_module = strtolower($ns_parts[count($ns_parts) - 1]);

      if($ns_parts[0] !== 'Forge')
        return;
      



      unset($ns_parts[0]);
      unset($ns_parts[1]);

      $base_path = static::getBasePath($ns_cls);
      if($base_path === null)
        return;

      $path = $base_path . implode('/' , $ns_parts);


      $regex_params = array_key_exists($ns_module, $mapping) ? $mapping[$ns_module] : $mapping['__default__'];
      $path .= '/' . preg_replace($regex_params[0], $regex_params[1], $cls);



      echo "<pre>";
      var_dump($ns_module);
      var_dump($regex_params[0], $regex_params[1], $cls);
      var_dump($path);
      echo "</pre>";
      $path = strtolower($path);
      if(file_exists($path))
        require_once($path);

    }

}
spl_autoload_register(__NAMESPACE__ ."\\Autoloader::loadClass");