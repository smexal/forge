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
    $this->modified = $page['modified'];
    $this->created = $page['created'];
    $this->url = $page['url'];
    $this->status = $page['status'];
  }

  public function author() {
    return new User($this->creator);
  }

}

?>
