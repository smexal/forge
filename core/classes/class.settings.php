<?
class Settings {

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

}
