<?php
/**
 * Provides shorthand definitions of regularly used methods inside the
 * Forge Project.
 */
function i($stringid, $domain=false, $lang=false) {
    return \Forge\Core\Classes\Localization::stringTranslation($stringid, $domain, $lang);
}

function triggerModifier() {
    return call_user_func_array(array(\Forge\Core\App\ModifyHandler::instance(), 'trigger'), func_get_args());
}

function fireEvent() {
    return call_user_func_array(array(\Forge\Core\App\EventHandler::instance(), 'fire'), func_get_args());
}

function registerModifier() {
    return call_user_func_array(array(\Forge\Core\App\ModifyHandler::instance(), 'add'), func_get_args());
}

function registerEvent() {
    return call_user_func_array(array(\Forge\Core\App\EventHandler::instance(), 'register'), func_get_args());
}
