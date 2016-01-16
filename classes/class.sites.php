<?php

class Sites {
    private $app;
    private $data = false;
    private $fields = array(
        'id',
        'parent',
        'sequence',
        'name',
        'modified',
        'created',
        'creator'
    );

    public function __construct($id) {
        $this->app = App::instance();
        $this->data['id'] = $id;
    }

    public function getData() {
        $this->app->db->where('id', $this->data['id']);
        $site = $this->app->db->getOne('sites');
        foreach($this->fields as $field) {
            $this->data[$field] = $site[$field];
        }
    }

    public function get($field) {
        if(array_key_exists($field, $this->data)) {
            return $this->data[$field];
        } else {
            if(in_array($field, $this->fields)) {
                $this->getData();
                if(array_key_exists($field, $this->data)) {
                    return $this->data[$field];
                } else {
                    Logger::error(sprintf(i("Queried field '%1$s' which does not exist")), $field);
                }
            }
        }
    }
    
    public static function create($name, $parent = 0) {
      if(! Auth::allowed("manage.sites.add")) {
        return;
      }
      if(strlen($name) == 0) {
        $name = i('Untitled');
      }
      
      $app = App::instance();
      // add number of amount +1 to name, to idenfity "duplicates"
      $no = 1;
      do {
        if($no > 1) {
          $app->db->where("name", $name . " " .$no);
        } else {
          $app->db->where("name", $name);
        }
        $no++;
        $app->db->get("sites");
        $amount = $app->db->count;
      } while ($amount > 0);
      $no = $no - 1;
      if($no > 1)
        $name = $name . " " . $no;
      
      // find right sequence
      $app->db->where("parent", $parent);
      $app->db->get("sites");
      if($app->db->count > 0) {
        $sequence = $app->db->count;
      } else {
        $sequence = 0;
      }
      $data = array(
          'sequence' => $sequence,
          'parent' => $parent,
          'name' => $name,
          'creator' => $app->user->get('id')
      );
      
      $app->db->insert('sites', $data);
      return $name;
    }
}

?>
