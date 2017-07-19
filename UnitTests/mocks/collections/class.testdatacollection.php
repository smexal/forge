<?php

namespace Forge\Core\Tests;

use \Forge\Core\Abstracts\DataCollection;
use \Forge\Core\App\App;
use \Forge\Core\App\Auth;
use \Forge\Core\Classes\User;
use \Forge\Core\Classes\Utils;



class TestCollection extends DataCollection {
    private $itemId = null;

    protected function setup() {
        $this->preferences['name'] = 'testcollection';
        $this->preferences['title'] = i('Test Collections', 'forge-quests');
        $this->preferences['all-title'] = i('Manage Test Collections', 'forge-quests');
        $this->preferences['add-label'] = i('Add Test Collection', 'forge-quests');
        $this->preferences['single-item'] = i('Test Collection', 'forge-quests');


        $this->custom_fields();

    }

    public function render($item) {
    }

    public function customEditContent($id) {}

    private function custom_fields() {}
}
?>