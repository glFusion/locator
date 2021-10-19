<?php
/**
*   Default installation values for the Locator plugin for glFusion
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2018 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.1.4
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*   GNU Public License v2 or later
*               @filesource
*/

if (!defined('GVERSION')) {
    die('This file can not be used on its own!');
}

global $_CONF;
$parts = preg_split('/[_-]/', $_CONF['locale']);
$def_country = strtoupper(array_pop($parts));

global $locatorConfigData;
$locatorConfigData = array(
    array(
        'name' => 'sg_main',
        'default_value' => NULL,
        'type' => 'subgroup',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'fs_main',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'default_radius',
        'default_value' => '30',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 10,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'distance_unit',
        'default_value' => 'miles',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 11,
        'sort' => 20,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'show_map',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 30,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'submission',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 40,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'submit',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 15,
        'sort' => 50,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'displayblocks',
        'default_value' => 3,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 13,
        'sort' => 60,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'profile_showmap',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 70,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'usermenu_option',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 80,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'use_weather',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 90,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'use_directions',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 100,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'api_only',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 3,
        'sort' => 110,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'def_country',
        'default_value' => $def_country,
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 120,
        'set' => true,
        'group' => 'locator',
    ),

    // Google API settings
    array(
        'name' => 'fs_mappers',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'autofill_coord',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 3,
        'sort' => 10,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'mapper',
        'default_value' => 'openstreetmap',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 20,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'geocoder',
        'default_value' => 'openstreetmap',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 30,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'google_api_key',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 40,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'google_js_key',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 50,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'google_js_key',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 60,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'geocodio_api_key',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 70,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'mapquest_key',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 50,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'osm_use_tileserver',
        'default_value' => false,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 3,
        'sort' => 60,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'here_rest_key',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 90,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'here_js_key',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 100,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'mapbox_token',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 10,
        'selection_array' => 0,
        'sort' => 110,
        'set' => true,
        'group' => 'locator',
    ),

    // Permissions fieldset
    array(
        'name' => 'fs_permissions',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'defgrp',
        'default_value' => 13,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 0,
        'sort' => 10,
        'set' => true,
        'group' => 'locator',
    ),
    array(
        'name' => 'default_permissions',
        'default_value' => array (3, 3, 2, 2),
        'type' => '@select',
        'subgroup' => 0,
        'fieldset' => 20,
        'selection_array' => 12,
        'sort' => 20,
        'set' => true,
        'group' => 'locator',
    ),
);


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
    global $locatorConfigData;

    $c = config::get_instance();
    if (!$c->group_exists('locator')) {
        USES_lib_install();
        foreach ($locatorConfigData AS $cfgItem) {
            _addConfigItem($cfgItem);
        }
    }
    return true;
}

?>
