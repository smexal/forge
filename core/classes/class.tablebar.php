<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

class TableBar {
    private $api = false;
    private $target = false;

    private $search = false;
    private $sorting = false;
    private $directFilters = [];

    public function __construct($api, $target) {
        $this->api = $api;
        $this->target = $target;
    }

    public function enableSearch() {
        $this->search = true;
    }

    public function enableSorting($sort) {
        if($this->sorting)
            return;
        $this->sorting = $sort;
    }

    public function render() {
        return App::instance()->render(CORE_TEMPLATE_DIR.'assets/', 'tablebar', [
          'api' => $this->api,
          'target' => $this->target,
          'contentleft' => $this->getTableBarLeft(),
          'contentright' => $this->getTableBarRight() 
      ]);
    }

    public function addDirectFilter($data) {
        // TODO.
        $this->directFilters[] = $data;
    }

    private function getTableBarLeft() {
      $return = '';

      if($this->search) {
          $return.= Fields::text([
              'label' => i('Search', 'core'),
              'key' => 'search'
          ]);
      }
      foreach($this->directFilters as $filter) {
        array_unshift($filter['values'], i('Nothing selected'));
        $return.= Fields::select([
            'label' => $filter['label'],
            'key' => 'filter__'.$filter['field'],
            'values' => $filter['values']
        ]);
      }

      return $return;
    }

    private function getTableBarRight() {
      $return = '';

      if($this->sorting) {
        $return.= Fields::select([
            'label' => i('Sorting', 'core'),
            'key' => 'sorting',
            'values' => $this->sorting
        ]);
      }

      return $return;
    }

}

?>