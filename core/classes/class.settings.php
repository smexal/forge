<?php

namespace Forge\Core\Classes;

use \Forge\Core\App\App;

class Settings {
    private static $instance = null;
    private $allowedPositions = array('left', 'right');
    public $fields = array();
    public $tabs = array();

    public static function get($k) {
        App::instance()->db->where('keey', $k);
        $data = App::instance()->db->getOne('settings', 'value');
        if(is_null($data))
            return;
        return $data['value'];
    }

    public static function addTab($id, $title) {
        $inst = self::instance();
        array_push($inst->tabs, array(
            'id' => $id,
            'title' => $title,
            'active' => false
        ));
    }

    public static function getProfileFields() {
        $fields = [
            'forename' => [
                'type' => 'text',
                'args' => [
                    'label' => i('Forename', 'core'),
                    'key' => 'user_forename'
                ]
            ],
            'surename' => [
                'type' => 'text',
                'args' => [
                    'label' => i('Last name', 'core'),
                    'key' => 'user_surname'
                ]
            ],
            'birthdate' => [
                'type' => 'text',
                'args' => [
                    'label' => i('Birthdate', 'core'),
                    'key' => 'user_birthdate'
                ]
            ],
            'address' => [
                'type' => 'text',
                'args' => [
                    'label' => i('Address', 'core'),
                    'key' => 'user_address'
                ]
            ],
            'zip_place' => [
                'type' => 'text',
                'args' => [
                    'label' => i('ZIP/Place', 'core'),
                    'key' => 'user_zip_place'
                ]
            ],
            'phone' => [
                'type' => 'text',
                'args' => [
                    'label' => i('Phone Number', 'core'),
                    'key' => 'user_phone'
                ]
            ], 
        ];
        foreach($fields as $key => $field) {
            $fields[$key]['display_state'] = Settings::get('profile_'.$key);
        }
        return $fields;
    }

    public function tabs() {
        $return = array();
        foreach($this->tabs as $tab) {
            $skip = false;
            foreach(App::instance()->mm->getActiveModules() as $mod) {
                if($mod == $tab['id']) {
                    $skip = true;
                    break;
                }
            }
            if(! $skip) {
                array_push($return, $tab);
            }
        }
        return $return;
    }

    public static function set($key, $value) {
        $db = App::instance()->db;

        $db->where('keey', $key);
        $db->get('settings');
        if($db->count > 0) {
            $db->where('keey', $key);
            $db->update('settings', array(
                'value' => $value
            ));
        } else {
            $db->insert('settings', array(
                'keey' => $key,
                'value' => $value
            ));
        }
        if($value === false) {
            $db->where('keey', $key);
            $db->delete('settings');
        }
    }

    public function registerField($field, $key, $position='left', $tab='general') {
        if(in_array($position, $this->allowedPositions)) {
            $this->fields[$tab][$position][$key] = $field;
        }
    }

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct(){}
    private function __clone(){}

}
