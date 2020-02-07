<?php

namespace Forge\Core\Classes;

use Forge\Core\App\App;



class Pagination {
    private $totalPages = 1;
    private $itemAmount = 1;
    private $currentPage = 1;
    private $paginationSize = PAGINATION_SIZE;

    public function __construct($items, $current) {
        $this->itemAmount = $items;
        $this->currentPage = $current;
    }

    public function render() {
        $pageAmount = ceil($this->itemAmount / $this->paginationSize);
        $pages = range(1, $pageAmount);
        return App::instance()->render(CORE_TEMPLATE_DIR.'assets/', 'pagination', [
            'pages' => $pages,
            'active' => $this->currentPage
        ]);
    }

    public function setPaginationSize($size) {
        $this->paginationSize = $size;
    }
}