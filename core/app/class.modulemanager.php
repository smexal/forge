<?

class ModuleManager {
  public $modules = array();

  public function __construct() {
    $this->modules = $this->getModules();
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
}


?>
