<?php
namespace Forge\Core\Migrations;

use \Forge\Core\Traits\Singleton;
use \Forge\Core\Interfaces\IMigration;

use \Forge\Core\App\App;

class Core_1_1_2Migration implements IMigration {
    use Singleton;
    
    public static function identifier() {
        return 'core';
    }

    public static function targetversion() {
        return '1.1.2';
    }

    public static function oninstall() {
        return true;
    }

    public static function prepare() {

    }

    public static function execute() {
        $stmts = [];
        try {
            App::instance()->db->startTransaction();
            App::instance()->db->query(
                'CREATE TABLE `relations` (
                    `id` int(11) NOT NULL,
                    `item_left` int(11) NOT NULL,
                    `item_right` int(11) NOT NULL,
                    `direction` int(2) NOT NULL DEFAULT 3,
                    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`item_left`) 
                        REFERENCES `collections` (id)
                        ON DELETE CASCADE,
                    FOREIGN KEY (`item_right`) 
                        REFERENCES `collections` (id)
                        ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
            );
            App::instance()->db->commit();
        } catch (Exception $e) {
                App::instance()->db->rollback();
        }

    }
}