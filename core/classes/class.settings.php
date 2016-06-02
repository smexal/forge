<?
class Settings {
    private static $instance = null;
    private $allowedPositions = array('left', 'right');
    public $fields = array();

    public static function get($k) {
        App::instance()->db->where('keey', $k);
        $data = App::instance()->db->getOne('settings', 'value');
        return $data['value'];
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
    }

    public function registerField($field, $key, $position='left') {
        if(in_array($position, $this->allowedPositions)) {
            $this->fields[$position][$key] = $field;
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
