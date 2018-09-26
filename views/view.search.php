<?php

namespace Forge\Views;

use Forge\Core\Abstracts\View;
use Forge\Core\Classes\Search;
use Forge\Core\Classes\Utils;



class SearchView extends View {
    private $message = false;
    public $name = 'search';

    public function content($uri=[]) {
        return $this->app->render(CORE_TEMPLATE_DIR.'views/', 'search', [
            'base' => Utils::getCurrentUrl(),
            'title' => i('Search'),
            'query' => Search::getQuery(),
            'results' => Search::getResults(),
            'noresults' => sprintf(i('No search results for "%1$s"'), Search::getQuery()),
        ]);
    }
}
