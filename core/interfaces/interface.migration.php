<?php

namespace Forge\Core\Interfaces;

interface IMigration {
    public static function identifier();
    public static function targetversion();
    public static function oninstall();
    public static function instance();
    public static function prepare();
    public static function execute();
}

