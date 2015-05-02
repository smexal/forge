<?php

class Group {
    private $app = null;
    public $id = null;

    public function __construct($id) {
        if(is_numeric($id)) {
            $this->app = App::instance();
            $this->id = $id;
        } else {
            Logger::error("Tried to instance of Group with id '".$id."'");
        }
    }

    public function memberCount() {
        $this->app->db->where("groupid", $this->id);
        $this->app->db->get("groups_users");
        return $this->app->db->count;
    }

}

?>