<?

class CollectionManager {
  public $collections = array();

  public function __construct() {
    $this->collections = $this->getCollections();
  }

  public function add($args) {
    $db = App::instance()->db;
    $db->insert('collections', array(
      'sequence' => 0,
      'name' => $args['name'],
      'type' => $args['type'],
      'settings' => '',
      'author' => App::instance()->user->get('id')
    ));
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

  public function deleteCollectionItem($id) {
    $db = App::instance()->db;
    $db->where('id', $id);
    $db->delete('collections');
  }
}


?>
