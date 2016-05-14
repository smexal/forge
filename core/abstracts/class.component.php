<?php

abstract class Component implements IComponent {
    public $id = null;
    public $prefs = array();
    protected $defaults = array(
        'description' => '',
        'image' => '',
        'level' => 'root',
        'container' => false
    );

    public function __construct() {
        $this->prefs = $this->prefs();
    }

    public function getPref($key) {
        if(array_key_exists($key, $this->prefs)) {
            return $this->prefs[$key];
        } else {
            return $this->returnDefault($key);
        }
    }

    public function settings() {
        $savedPrefs = $this->getSavedPrefs();
        foreach($this->settings as $key => $value) {
            if(array_key_exists($value['key'], $savedPrefs)) {
                $this->settings[$key]['saved_value'] = $savedPrefs[$value['key']];
            }
        }

        return $this->settings;
    }

    public function savePref($key, $value) {
        $savedPrefs = $this->getSavedPrefs(true);
        $savedPrefs[$key] = $value;

        $db = App::instance()->db;
        $db->where('id', $this->getId());
        $db->update('page_elements', array(
            'prefs' => json_encode($savedPrefs)
        ));
    }

    protected function returnDefault($key) {
        if(array_key_exists($key, $this->defaults)) {
            return $this->defaults[$key];
        } else {
            Logger::debug("Unknown Pref '".$key."' for Component '".get_called_class()."'");
            return false;
        }
    }

    public function getId() {
        if(!$this->id) {
            return;
        }
        return $this->id;
    }

    public function getSavedPrefs($forceUpdate=false) {
        if($forceUpdate) {
            $this->setPageElementPrefs();
        }
        if($this->savedPrefsReady()) {
            $saved = json_decode($this->prefs['page_element_prefs']['prefs'], true);
        }
        if(is_null($saved)) {
            return array();
        }
        return $saved;
    }

    public function getPage() {
        if($this->savedPrefsReady()) {
            return $this->prefs['page_element_prefs']['pageid'];
        }
        return false;
    }

    private function savedPrefsReady() {
        if(!$this->id) {
            return false;
        }
        if(!array_key_exists('page_element_prefs', $this->prefs)) {
            $this->setPageElementPrefs();
        }
        return true;
    }

    private function setPageElementPrefs() {
        $db = App::instance()->db;
        $db->where('id', $this->id);
        $db_el = $db->getOne('page_elements');
        $this->prefs['page_element_prefs'] = array(
            'pageid' => $db_el['pageid'],
            'prefs' => $db_el['prefs'],
            'parent' => $db_el['parent'],
            'lang' => $db_el['lang'],
            'position' => $db_el['position']
        );
    }

    public function getField($key) {
        if($this->savedPrefsReady()) {
            $prefs = $this->getSavedPrefs();
            if(array_key_exists($key, $prefs)) {
                return $prefs[$key];
            }
            return false;
        }
        return false;
    }

    public function getChildrenBuilderContent($position_x) {
        $com = App::instance()->com;
        $content = '';
        foreach($com->getChildrenOf($this->getId(), $position_x) as $component) {
            if(is_null($component)) {
                continue;
            }
            $content.= $component->getBuilderContent();
        }
        return $content;
    }

    public function getBuilderContent() {
        return App::instance()->render(CORE_TEMPLATE_DIR."components/builder/", "default", array(
            'name' => $this->getPref('name'),
            'edit' => array(
                'link' => Utils::getUrl(array('manage', 'pages', 'edit-element', $this->getId())),
                'name' => i('Edit')
            ),
            'remove' => array(
                'link' => Utils::getUrl(array('manage', 'pages', 'remove-element', $this->getId())),
                'name' => i('Remove')
            ),
            'custom' => $this->customBuilderContent()
        ));
    }

    public function customBuilderContent() {
        return false;
    }

    public function parent() {
        if($this->savedPrefsReady()) {
            return $this->prefs['page_element_prefs']['parent'];
        }
        return 0;
    }

    public function getPrefSet() {
        return $this->prefs;
    }

    public function prefs() {
        return false;
    }
}

?>
