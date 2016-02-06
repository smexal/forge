<?

class CollectionManager {
  public $collections = array();

  public function __construct() {
    $this->collections = $this->getCollections();
  }

  public function getCollections() {
      $classes = get_declared_classes();
      $implementsIModule = array();
      foreach($classes as $klass) {
          $reflect = new ReflectionClass($klass);
          if($reflect->implementsInterface('IDataCollection')) {
              $rc = new ReflectionClass($klass);
              if(! $rc->isAbstract())
                  $implementsIModule[] = $klass;
          }
      }
      $collections = array();
      foreach($implementsIModule as $collection) {
        $collections[] = $collection::instance();
      }
      return $collections;
  }
}


?>
