<?php
namespace Forge\Core\Migrations;

use \Forge\Core\Traits\Singleton;
use \Forge\Core\Interfaces\IMigration;

use \Forge\Core\App\App;

class Core_1_0_0Migration implements IMigration {
    use Singleton;
    
    public static function identifier() {
        return 'core';
    }

    public static function targetversion() {
        return '1.0.0';
    }

    public static function oninstall() {
        return true;
    }

    public static function prepare() {

    }

    public static function execute() {
        try {
            App::instance()->db->startTransaction();
            App::instance()->db->query(
                'CREATE TABLE IF NOT EXISTS `relations` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(32) NOT NULL,
                    `item_left` int(11) NOT NULL,
                    `item_right` int(11) NOT NULL,
                    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
            );

            App::instance()->db->query(
                'ALTER TABLE `page_elements` ADD `builderId` VARCHAR(150) NOT NULL DEFAULT \'none\' AFTER `position_x`, ADD INDEX `builderId` (`builderId`);'
            );

            App::instance()->db->query(
                'ALTER TABLE `navigation_items` ADD `direct` VARCHAR(600) NULL AFTER `lang`;'
            );

            App::instance()->db->commit();
        } catch (Exception $e) {
            App::instance()->db->rollback();
        }

    }
}