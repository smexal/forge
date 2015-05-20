<?php

class StringTranslationUpdateManagement extends AbstractView {
    public $parent = 'string-translation';
    public $name = 'update';
    public $permission = 'manage.locales.strings.update';

    public function content($uri=array()) {
      if(count($uri) > 0) {
        if($uri[0] == 'run') {
          $this->runUpdate();
        }
      } else {
        return $this->app->render(TEMPLATE_DIR."views/parts/", 'progress', array(
          'title' => i('String translation update running'),
          'url' => Utils::getUrl(array("manage", "string-translation", "update", "run")),
          'targeturl' => Utils::getUrl(array("manage", "string-translation")),
        ));
      }
    }

    private function runUpdate() {
      Utils::octetStream();

      // Count to 20, outputting each second
      for ($i = 0;$i < 5; $i++) {
          echo '<p>'.$i.'</p>';
          flush();
          sleep(1);
      }

      App::instance()->addMessage(i('All Strings have been updated.'), "success");
    }
}

?>
