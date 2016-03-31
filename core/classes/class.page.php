<?

class Page {
  public $id, $parent, $sequence, $name, $modified, $created, $creator, $url, $status;

  public function __construct($id) {
    App::instance()->db->where('id', $id);
    $page = App::instance()->db->getOne('pages');
    $this->id = $page['id'];
    $this->parent = $page['parent'];
    $this->sequence = $page['sequence'];
    $this->name = $page['name'];
  }

}

?>
