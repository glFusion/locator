<?php
/**
*   Default installation values for the Locator plugin for glFusion
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.1.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*   GNU Public License v2 or later
*               @filesource
*/

if (!defined('GVERSION')) {
    die('This file can not be used on its own!');
}

/**
*   Default values to be used during plugin installation/upgrade
*   @global array $_GEO_DEFAULT
*/
global $_GEO_DEFAULT;
$_GEO_DEFAULT= array();

/**
*   Plugin-specific configuration values
*/
global $_CONF_GEO;

$_GEO_DEFAULT['default_radius'] = 30;   // Default search radius
$_GEO_DEFAULT['google_api_key'] = '';   // User must supply their own
$_GEO_DEFAULT['autofill_coord'] = false; // Set false since it won't work without a Google key
$_GEO_DEFAULT['show_map'] = true;      // Key no longer required for map
//$_GEO_DEFAULT['url_geocode']  = 'http://maps.google.com/maps/geo?q=%address%&output=csv&key=%google_key%';
$_GEO_DEFAULT['distance_unit'] = 'miles';  // 'km' for kilometers, else = miles
$_GEO_DEFAULT['submission'] = true;     // use submission queue
$_GEO_DEFAULT['anon_submit'] = false;   // allow anon submissions
$_GEO_DEFAULT['user_submit'] = true;    // allow non-admin submissions
$_GEO_DEFAULT['displayblocks'] = 3;     // show left & right blocks
$_GEO_DEFAULT['purge_userlocs'] = 14;   // Days to keep user-entered origins
$_GEO_DEFAULT['usermenu_option'] = 1;   // Show link on the user menu?
$_GEO_DEFAULT['profile_showmap'] = 0;   // Show a map in the user profile?
$_GEO_DEFAULT['use_weather'] = 0;       // Integrate with the Weather plugin?
$_GEO_DEFAULT['use_directions'] = 1;    // Get directions from Google?

// Set the default permissions
$_GEO_DEFAULT['default_permissions'] =  array (3, 3, 2, 2);
$_GEO_DEFAULT['defgrp'] = 13;       // logged-in users as default group

// Is the plugin active, or just providing API functions?
$_GEO_DEFAULT['api_only'] = 0;

/**
*   Initialize Locator plugin configuration
*
*   Creates the database entries for the configuation if they don't already
*   exist. Initial values will be taken from $_CONF_GEO if available (e.g. from
*   an old config.php), uses $_GEO_DEFAULT otherwise.
*
*   @param  integer $group_id  Group ID to use, Default if zero
*   @return boolean true: success; false: an error occurred
*/
function plugin_initconfig_locator($group_id = 0)
{
    global $_CONF, $_CONF_GEO, $_GEO_DEFAULT;

    if (is_array($_CONF_GEO) && (count($_CONF_GEO) > 1)) {
        $_GEO_DEFAULT = array_merge($_GEO_DEFAULT, $_CONF_GEO);
    }

    // Use configured default if a valid group ID wasn't presented
    if ($group_id == 0)
        $group_id = $_GEO_DEFAULT['defgrp'];

    $c = config::get_instance();

    if (!$c->group_exists($_CONF_GEO['pi_name'])) {

        $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, $_CONF_GEO['pi_name']);
        $c->add('fs_main', NULL, 'fieldset', 0, 0, NULL, 0, true, $_CONF_GEO['pi_name']);

        $c->add('default_radius', $_GEO_DEFAULT['default_radius'],
                'text', 0, 0, 0, 10, true, $_CONF_GEO['pi_name']);

        $c->add('distance_unit', $_GEO_DEFAULT['distance_unit'], 
                'select', 0, 0, 11, 20, true, $_CONF_GEO['pi_name']);
        
        $c->add('show_map', $_GEO_DEFAULT['show_map'], 
                'select', 0, 0, 3, 30, true, $_CONF_GEO['pi_name']);

        $c->add('submission', $_GEO_DEFAULT['submission'], 
                'select', 0, 0, 3, 40, true, $_CONF_GEO['pi_name']);

        $c->add('anon_submit', $_GEO_DEFAULT['anon_submit'], 
                'select', 0, 0, 3, 50, true, $_CONF_GEO['pi_name']);

        $c->add('user_submit', $_GEO_DEFAULT['user_submit'], 
                'select', 0, 0, 3, 60, true, $_CONF_GEO['pi_name']);

        $c->add('displayblocks', $_GEO_DEFAULT['displayblocks'],
                'select', 0, 0, 13, 70, true, $_CONF_GEO['pi_name']);

        $c->add('profile_showmap', $_GEO_DEFAULT['profile_showmap'], 
               'select', 0, 0, 3, 80, true, $_CONF_GEO['pi_name']);

        $c->add('usermenu_option', $_GEO_DEFAULT['usermenu_option'], 
               'select', 0, 0, 3, 90, true, $_CONF_GEO['pi_name']);

        $c->add('use_weather', $_GEO_DEFAULT['use_weather'], 
               'select', 0, 0, 3, 90, true, $_CONF_GEO['pi_name']);

        $c->add('use_directions', $_GEO_DEFAULT['use_directions'], 
               'select', 0, 0, 3, 100, true, $_CONF_GEO['pi_name']);

        $c->add('api_only', $_GEO_DEFAULT['api_only'], 
               'select', 0, 0, 3, 110, true, $_CONF_GEO['pi_name']);

        // Geocoding Settings
        $c->add('fs_google', NULL, 'fieldset', 0, 1, NULL, 0, true, $_CONF_GEO['pi_name']);

        $c->add('autofill_coord', $_GEO_DEFAULT['autofill_coord'], 
                'select', 0, 1, 3, 10, true, $_CONF_GEO['pi_name']);

        $c->add('google_api_key', $_GEO_DEFAULT['google_api_key'], 
                'text', 0, 1, 0, 20, true, $_CONF_GEO['pi_name']);

        //$c->add('url_geocode', $_GEO_DEFAULT['url_geocode'], 
        //        'text', 0, 1, 0, 30, true, $_CONF_GEO['pi_name']);

        // Permissions
        $c->add('fs_permissions', NULL, 'fieldset', 0, 4, NULL, 0, true, $_CONF_GEO['pi_name']);
        $c->add('defgrp', $group_id,
                'select', 0, 4, 0, 10, true, $_CONF_GEO['pi_name']);
        $c->add('default_permissions', $_GEO_DEFAULT['default_permissions'],
                '@select', 0, 4, 12, 20, true, $_CONF_GEO['pi_name']);

    }

    return true;
}

?>
