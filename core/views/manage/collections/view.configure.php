<?php

namespace Forge\Core\Views\Manage\Collections;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Form;
use \Forge\Core\Classes\Localization;
use \Forge\Core\Classes\Utils;

use function \Forge\Core\Classes\i;

class ConfigureView extends View {
    public $parent = 'collections';
    public $name = 'configure';
    public $permission = 'manage.collections.configure';
    public $events = array(
        'onUpdateCollectionConfiguration'
    );
    private $collection = false;

    private function getCollection() {
        $uri = Utils::getUriComponents();
        return $uri[count($uri)-2];
    }

    public function onUpdateCollectionConfiguration() {
        $manager = App::instance()->cm;
        $this->collection = $manager->getCollection($this->getCollection());

        $lang = Localization::getCurrentLanguage();

        foreach( $this->collection->configuration() as $configuration) {
            if(array_key_exists($configuration['key']."_".$lang, $_POST)) {
                $this->collection->saveSetting($configuration['key'], $_POST[$configuration['key']."_".$lang]);
            }
            if(array_key_exists($configuration['key'], $_POST)) {
                $this->collection->saveSetting($configuration['key'], $_POST[$configuration['key']], 0);
            }
        }
    }

    public function content($uri=array()) {
        $manager = App::instance()->cm;
        $this->collection = $manager->getCollection($this->getCollection());

        return App::instance()->render(CORE_TEMPLATE_DIR.'views/parts/', 'crud.modify', array(
            'title' => sprintf(i('Configure %s', 'core'), $this->collection->getPref('title')),
            'message' => false,
            'form' => $this->getForm()
        ));
    }

    private function getForm() {
        $form = new Form(Utils::getUrl(array('manage', 'collections', $this->getCollection(), 'configure')));
        $form->ajax(".content");
        $form->disableAuto();
        $form->hidden("event", $this->events[0]);
        $form->hidden("collection_type", $this->getCollection());

        foreach($this->collection->configuration() as $configuration) {
            $lang_preset = '';
            if($configuration['multilang'] === true) {
                $lang_preset = "_".Localization::getCurrentLanguage();
            }
            switch($configuration['type']) {
                case 'text':
                    $form->input(
                        $configuration['key'].$lang_preset,
                        $configuration['key'].$lang_preset,
                        $configuration['label'],
                        'input',
                        $this->collection->getSetting($configuration['key']),
                        $configuration['hint']
                    );
                    break;
                default:
                    break;
            }
        }

        $form->submit(i('Save'));
        return $form->render();
    }
}

