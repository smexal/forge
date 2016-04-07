<?php
class Fields {

    public static function build($args) {
        switch($args['type']) {
            case 'text':
                return self::text($args);
                break;
        }
    }

    /** a tabs example from bootstrap

    <div class="tab-content">
      <div id="home" class="tab-pane fade in active">
        <h3>HOME</h3>
        <p>Some content.</p>
      </div>
    </div>

    **/

    public static function text($args) {
        if($args['multilang'] && count(Localization::getActiveLanguages() > 1)) {
            $output = self::languageTabs($args);
        }
        return $output;
    }

    public static function getLangKey($lang, $key) {
        return $lang.'-'.$key;
    }

    public static function languageTabs($args) {
        $tabs = array();
        $first = true;
        foreach(Localization::getActiveLanguages() as $lang) {
            array_push($tabs, array(
                'name' => strtoupper($lang['code']),
                'active' => $first,
                'key' => self::getLangKey($lang['code'], $args['key'])
            ));
            $first = false;
        }
        return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "tabs", array(
            'tabs' => $tabs
        ));
    }

}


?>
