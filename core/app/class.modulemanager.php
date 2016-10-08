<?

class ModuleManager {
  private $app = null;
  public $modules = array();
  public $activeModuleObjects = array();

  public function __construct() {
    $this->app = App::instance();
    $this->modules = $this->getModules();
  }

  public function start() {
      // start all active plugins
      $active = $this->getActiveModules();
      foreach($this->modules as $module) {
          if(in_array($module->id, $active)) {
              array_push($this->activeModuleObjects, $module);
              $module->start();
              App::instance()->vm->getViews();
          }
      }
  }

  public function getModules() {
      $classes = get_declared_classes();
      $implementsIModule = array();
      foreach($classes as $klass) {
          $reflect = new ReflectionClass($klass);
          if($reflect->implementsInterface('IModule')) {
              $rc = new ReflectionClass($klass);
              if(! $rc->isAbstract())
                  $implementsIModule[] = $klass;
          }
      }
      $modules = array();
      foreach($implementsIModule as $module) {
        $modules[] = $module::instance();
      }
      return $modules;
  }

  public function deactivate($module) {
    $this->app->db->where('module', $module);
    $this->app->db->delete('modules');
  }

  public function activate($module) {
    $this->app->db->where('module', $module);
    $this->app->db->get('modules');
    if($this->app->db->count > 0) {
      Logger::info(sprintf(i('Tried to activate plugin, which is already active: %1$s'), $module));
      return;
    }
    $this->app->db->insert('modules', array(
      'module' => $module
    ));
  }

  public function getActiveModules() {
    $modules = $this->app->db->get('modules');
    $return = array();
    foreach($modules as $module) {
      array_push($return, $module['module']);
    }
    return $return;
  }

  public function isActive($moduleName) {
    $this->app->db->where('name', $moduleName);
    $modules = $this->app->db->get('modules');
    if($this->app->db->count > 0) {
      return true;
    }
    return false;
  }
}


?>
