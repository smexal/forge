<?php

namespace Forge\Core\Classes;

use Forge\Core\App\App;



class Pagination {
    private $itemAmount = 1;
    private $currentPage = 1;
    private $dotstoone = false;
    private $dotstolast = false;
    private $paginationSize = PAGINATION_SIZE;

    public function __construct($items, $current) {
        $this->itemAmount = $items;
        $this->currentPage = $current;
    }

    public function render() {
        $pageAmount = ceil($this->itemAmount / $this->paginationSize);
        $pages = range(1, $pageAmount);
        if(count($pages) > 5) {
            $pages_cropped = []; //
            for($index = $this->currentPage - 3; $index < $this->currentPage + 3; $index++) {
                if($index > 0 && $index < count($pages)) {
                    $pages_cropped[] = $index;
                }
            }
            if($pages_cropped[count($pages_cropped)-1] != count($pages)) {
                $this->dotstolast = true;
                $pages_cropped[] += count($pages);
            }
            if($this->currentPage - 3 > 1) {
                $this->dotstoone = true;
            }
        } else {
            $pages_cropped = $pages;
        }
        return App::instance()->render(CORE_TEMPLATE_DIR.'assets/', 'pagination', [
            'pages' => $pages_cropped,
            'active' => $this->currentPage,
            'dotstoone' => $this->dotstoone,
            'dotstolast' => $this->dotstolast,
        ]);
    }

    public function setPaginationSize($size) {
        $this->paginationSize = $size;
    }
}