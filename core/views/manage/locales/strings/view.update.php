<?php

namespace Forge\Core\Views\Manage\Locales\Strings;

use \Forge\Core\Abstracts\View;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class UpdateView extends View {
    public $parent = 'string-translation';
    public $name = 'update';
    public $permission = 'manage.locales.strings.update';
    private $progressBarId = "string-translation-bar";

    public function content($uri=array()) {
      if(count($uri) > 0) {
        if($uri[0] == 'run') {
          $this->runUpdate();
        }
      } else {
        return $this->app->render(CORE_TEMPLATE_DIR."views/parts/", 'progress', array(
          'title' => i('String translation update running'),
          'url' => Utils::getUrl(array("manage", "string-translation", "update", "run")),
          'targeturl' => false, // Utils::getUrl(array("manage", "string-translation"))
          'bar' => Utils::getProgressBar($this->progressBarId, 0)
        ));
      }
    }

    private function runUpdate() {
      Utils::octetStream();
      $this->app->stream(true);
      Localization::updateStrings(DOC_ROOT, true, $this->progressBarId);
      $this->app->stream(false);
    }
}

