<?php
/**
*   SQL Commands for the GeoLoc Plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2018 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.1.4
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

global $_SQL, $_TABLES, $_SQL_UPGRADE;
/**
*   Define tables used by the Locator plugin
*   @global array $_SQL
*/
$_SQL['locator_markers'] = 
"CREATE TABLE {$_TABLES['locator_markers']} (
  `id` varchar(20) NOT NULL DEFAULT '',
  `owner_id` mediumint(8) unsigned DEFAULT NULL,
  `title` varchar(60) NOT NULL DEFAULT '',
  `address` varchar(80) NOT NULL,
  `city` varchar(80) DEFAULT NULL,
  `state` varchar(80) DEFAULT NULL,
  `postal` varchar(80) DEFAULT NULL,
  `description` text,
  `lat` float(10,6) NOT NULL,
  `lng` float(10,6) NOT NULL,
  `is_origin` tinyint(1) DEFAULT '0',
  `keywords` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `views` mediumint(8) NOT NULL DEFAULT '0',
  `add_date` int(11) NOT NULL DEFAULT '0',
  `group_id` mediumint(8) unsigned DEFAULT NULL,
  `perm_owner` tinyint(1) unsigned NOT NULL DEFAULT '3',
  `perm_group` tinyint(1) unsigned NOT NULL DEFAULT '3',
  `perm_members` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `perm_anon` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM";

/** Marker submission table */
$_SQL['locator_submission'] = 
"CREATE TABLE `{$_TABLES['locator_submission']}` (
  `id` varchar(20) NOT NULL DEFAULT '',
  `owner_id` mediumint(8) unsigned DEFAULT NULL,
  `title` varchar(60) NOT NULL DEFAULT '',
  `address` varchar(80) NOT NULL,
  `city` varchar(80) DEFAULT NULL,
  `state` varchar(80) DEFAULT NULL,
  `postal` varchar(80) DEFAULT NULL,
  `description` text,
  `lat` float(10,6) NOT NULL,
  `lng` float(10,6) NOT NULL,
  `is_origin` tinyint(1) DEFAULT '0',
  `keywords` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `views` mediumint(8) NOT NULL DEFAULT '0',
  `add_date` int(11) NOT NULL DEFAULT '0',
  `group_id` mediumint(8) unsigned DEFAULT NULL,
  `perm_owner` tinyint(1) unsigned NOT NULL DEFAULT '3',
  `perm_group` tinyint(1) unsigned NOT NULL DEFAULT '3',
  `perm_members` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `perm_anon` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM";

/** Table to hold user's selected origins. */
$_SQL['locator_userXorigin'] = 
"CREATE TABLE `{$_TABLES['locator_userXorigin']}` (
  `id` mediumint(8) NOT NULL auto_increment,
  `uid` mediumint(8) default NULL,
  `mid` varchar(20) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idxUID` (`uid`)
) ENGINE=MyISAM";

/** Cache table to hold coordinates of user locations */
$_SQL['locator_userloc'] = 
"CREATE TABLE `{$_TABLES['locator_userloc']}` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL default 0,
  `type` tinyint(1) default '0',
  `location` varchar(80) default NULL,
  `add_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `lat` float(10,6),
  `lng` float(10,6),
  PRIMARY KEY  (`id`),
  UNIQUE KEY `location` (`uid`,`location`)
) ENGINE=MyISAM";

/** General cache table to hold geocoding lookups */
$_SQL['locator_cache'] =
"CREATE TABLE `{$_TABLES['locator_cache']}` (
  `cache_id` varchar(80) NOT NULL,
  `data` text,
  PRIMARY KEY (`cache_id`)
) Engine=MyISAM";

$_SQL_UPGRADE = array(
    '0.1.4' => array(
        "ALTER TABLE {$_TABLES['locator_userloc']} ADD type TINYINT(1) DEFAULT 0 AFTER id",
        "ALTER TABLE {$_TABLES['locator_userloc']} CHANGE add_date add_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        // new "enabled" field for markers
        "ALTER TABLE {$_TABLES['locator_markers']} ADD enabled TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'",
    ),
    '1.0.1' => array(
        // Add 'enabled' field to submissions that should have been in 0.1.4
        "ALTER TABLE {$_TABLES['locator_submission']} ADD enabled TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'",
    ),
    '1.1.1' => array(
        // Add 'enabled' field to submissions that should have been in 0.1.4
        "ALTER TABLE {$_TABLES['locator_userloc']} ADD uid INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`",
        "ALTER TABLE {$_TABLES['locator_userloc']} ADD UNIQUE `location` (`uid`, `location`)",
        "ALTER TABLE {$_TABLES['locator_markers']} ADD city varchar(80) AFTER address",
        "ALTER TABLE {$_TABLES['locator_markers']} ADD state varchar(80) AFTER city",
        "ALTER TABLE {$_TABLES['locator_markers']} ADD postal varchar(80) AFTER state",
        "ALTER TABLE {$_TABLES['locator_submission']} ADD city varchar(80) AFTER address",
        "ALTER TABLE {$_TABLES['locator_submission']} ADD state varchar(80) AFTER city",
        "ALTER TABLE {$_TABLES['locator_submission']} ADD postal varchar(80) AFTER state",
    ),
    '1.2.0' => array(
        "CREATE TABLE {$_TABLES['locator_cache']} (
            `cache_id` varchar(80) NOT NULL,
            `data` text,
            PRIMARY KEY (`cache_id`)
          ) Engine=MyISAM",
    ),
    '1.2.1' => array(
        "ALTER TABLE {$_TABLES['locator_markers']} ADD country varchar(3) AFTER postal",
    ),
);

?>
