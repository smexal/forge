<?php

function i($stringid, $domain=false, $lang=false) {
    return \Forge\Core\Classes\Localization::stringTranslation($stringid, $domain, $lang);
}
