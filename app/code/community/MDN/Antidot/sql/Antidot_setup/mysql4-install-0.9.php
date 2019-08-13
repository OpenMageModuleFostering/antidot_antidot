<?php

$installer = $this;

$installer->startSetup();

/**
 * Create table 'antidot/export'
 */
$installer->run("
CREATE TABLE `antidot_export` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT,
  `reference` VARCHAR(64) DEFAULT '',
  `type` ENUM('FULL', 'INC') NOT NULL DEFAULT 'FULL',
  `element` ENUM('CATALOG', 'CATEGORY') NOT NULL,
  `begin_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `items_processed` INT NOT NULL DEFAULT 0,
  `status` ENUM('SUCCESS', 'FAILED') NOT NULL,
  `error` VARCHAR(255) DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

$installer->endSetup();