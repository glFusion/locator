<?php
/**
 * Static configuration items for the Locator plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2018 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     v1.2.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

/**
 * Database table prefix.
 * @global string $_DB_table_prefix
 */
global $_DB_table_prefix;

/**
 * System array of database table names.
 * @global array $_TABLES
 */
global $_TABLES;

$_GEO_table_prefix = $_DB_table_prefix;

$_TABLES['locator_markers']     = $_GEO_table_prefix . 'locator_markers';
$_TABLES['locator_userXorigin'] = $_GEO_table_prefix . 'locator_userXorigin';
$_TABLES['locator_submission']  = $_GEO_table_prefix . 'locator_submission';
$_TABLES['locator_userloc']     = $_GEO_table_prefix . 'locator_userloc';
$_TABLES['locator_cache']       = $_GEO_table_prefix . 'locator_cache';

$_CONF_GEO['pi_version']        = '1.2.1';
$_CONF_GEO['pi_name']           = 'locator';
$_CONF_GEO['pi_display_name']   = 'Geo Locator';
$_CONF_GEO['gl_version']        = '1.7.0';
$_CONF_GEO['pi_url']            = 'http://www.leegarner.com';

// Define Google URLs, just to have them in one place
// Geocoding url, address will be appended to this
//define('GEO_GOOG_URL','https://maps.googleapis.com/maps/api/geocode/json?address=');
// URL to maps javascript
//define('GEO_MAP_URL', 'https://maps.google.com/maps/api/js?key=%s');

?>
