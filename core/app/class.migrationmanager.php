<?php

namespace Forge\Core\App;
use \Forge\Core\Abstracts\Manager;
use \Forge\Core\App\Modifier;
use \Forge\Core\Classes\Settings;

class MigrationManager extends Manager {
  public $migrations = null;

  protected static $file_pattern = '/(.*)migration\.([a-zA-Z_][a-zA-Z0-9_]*)\.php$/';
  protected static $class_suffix = 'Migration';

  public function __construct() {
    parent::__construct();
  }

  public function start() {
    $this->getMigrations();
    foreach($this->migrations as $key => $m_group) {
        $current = static::getCurrentVersion($key);

        foreach($m_group as $target => $migration) {
            if(!is_null($current) && version_compare($current, $target, '>=')) {
                continue;
            }
            if(is_null($current) && !$migration::oninstall()) {
                continue;
            }
            
            try {
                $migration::prepare();
                $migration::execute();
                static::setCurrentVersion($key, $target);
            } catch (Exception $e) {
                error_log("Migration for $key failed to switch from version $current to $target");
            }
        }
    }
  }

  private static function setCurrentVersion($key, $version) {
    Settings::set('migration-manager-' . $key, $version);
  }

  private static function getCurrentVersion($key) {
    return Settings::get('migration-manager-' . $key);
  }

  public function getMigrations() {
    if(is_array($this->migrations)) {
      return $this->migrations;
    }

    $migration_classes = $this->_getMigrations();
    $migrations = array();
    foreach($migration_classes as $migration) {
      if(!isset($migrations[$migration::identifier()])) {
        $migrations[$migration::identifier()] = [];
      }
      $migrations[$migration::identifier()][$migration::targetversion()] =  $migration::instance();
    }
    
    $this->migrations = $this->orderMigrations($migrations);
    return $this->migrations;
  }

  private function orderMigrations($migrations) {
    foreach($migrations as $key => &$m_group) {
        uksort($m_group, 'version_compare');
    }
    return $migrations;
  }

  private function _getMigrations() {
      App::instance()->eh->fire("ongetMigrations");
      $_REQUEST['test'] = true;
      $flush_cache = \triggerModifier('Forge/MigrationManager/FlushCache', MANAGER_CACHE_FLUSH === true);
      $classes = static::loadClasses($flush_cache);
      App::instance()->eh->fire("onLoadedMigrations", $classes);
      return $classes;
  }

}