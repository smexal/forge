<?php

namespace Forge\Core\Interfaces;

interface IMigration {
    /**
     * Unique identifier for this migration group.
     * 
     * This defines the group in which a migration checks its version
     * with all the other versions
     */
    public static function identifier();
    /**
     * On which version this migration is after it is done
     */
    public static function targetversion();
    /**
     * If this migration have to be executed upon the module
     * installation process.
     */
    public static function oninstall();
    /**
     * Only one instance of this class is allowed to exist
     * make sure it is a singleton.
     */
    public static function instance();
    /**
     * Executed before the execution
     */
    public static function prepare();
    /**
     * Execute the migration
     */
    public static function execute();
}

