<?php
/**
 * Automatic installation routine for the Locator plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2021 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.2.2
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/** Include the plugin's system functions */
require_once __DIR__ . '/functions.inc';

/** Include the database statements */
require_once __DIR__ . '/sql/mysql_install.php';

$INSTALL_plugin['locator'] = array(
    'installer' => array(
        'type' => 'installer',
        'version' => '1',
        'mode' => 'install',
    ),

    'plugin' => array(
        'type' => 'plugin',
        'name' => $_CONF_GEO['pi_name'],
        'ver' => $_CONF_GEO['pi_version'],
        'gl_ver' => $_CONF_GEO['gl_version'],
        'url' => $_CONF_GEO['pi_url'],
        'display' => $_CONF_GEO['pi_display_name'],
    ),

    array(
        'type' => 'table',
        'table' => $_TABLES['locator_markers'],
        'sql' => $_SQL['locator_markers'],
    ),

    array(
        'type' => 'table',
        'table' => $_TABLES['locator_submission'],
        'sql' => $_SQL['locator_submission'],
    ),

    array(
        'type' => 'table',
        'table' => $_TABLES['locator_userXorigin'],
        'sql' => $_SQL['locator_userXorigin'],
    ),

    array(
        'type' => 'table',
        'table' => $_TABLES['locator_userloc'],
        'sql' => $_SQL['locator_userloc'],
    ),

    array(
        'type' => 'table',
        'table' => $_TABLES['locator_cache'],
        'sql' => $_SQL['locator_cache'],
    ),

    array(
        'type' => 'feature',
        'feature' => 'locator.admin',
        'desc' => 'Locator Admin',
        'variable' => 'admin_feature_id',
    ),

    array(
        'type' => 'feature',
        'feature' => 'locator.view',
        'desc' => 'Can use the Locator plugin',
        'variable' => 'view_feature_id',
    ),

    array(
        'type' => 'feature',
        'feature' => 'locator.submit',
        'desc' => 'Bypass Locator Submission Queue',
        'variable' => 'submit_feature_id',
    ),

    array(
        'type' => 'mapping',
        'findgroup' => 'Root',
        'feature' => 'admin_feature_id',
        'log' => 'Adding feature to the admin group',
    ),

    array(
        'type' => 'mapping',
        'findgroup' => 'All Users',
        'feature' => 'view_feature_id',
        'log' => 'Adding feature to the All Users group',
    ),

    array(
        'type' => 'mapping',
        'findgroup' => 'Root',
        'feature' => 'submit_feature_id',
        'log' => 'Adding feature to the admin group',
    ),
    array(
        'type'  => 'mkdir',
        'dirs'  => array(
            $_CONF['path_html'] . '/data',
            $_CONF['path_html'] . '/data/' . $_CONF_GEO['pi_name'],
            $_CONF['path_html'] . '/data/' . $_CONF_GEO['pi_name'] . '/imgcache',
        ),
    ),
);


/**
 * Puts the datastructures for this plugin into the glFusion database.
 * Note: Corresponding uninstall routine is in functions.inc.
 *
 * @return  boolean True if successful False otherwise
 */
function plugin_install_locator()
{
    global $INSTALL_plugin, $_CONF_GEO;

    $pi_name            = $_CONF_GEO['pi_name'];
    $pi_display_name    = $_CONF_GEO['pi_display_name'];
    $pi_version         = $_CONF_GEO['pi_version'];

    COM_errorLog("Attempting to install the $pi_display_name plugin", 1);

    $ret = INSTALLER_install($INSTALL_plugin[$pi_name]);
    return $ret == 0 ? true : false;
}


/**
 * Loads the configuration records for the Online Config Manager.
 *
 * @return  boolean     true = proceed with install, false = an error occured
 */
function plugin_load_configuration_locator()
{
    global $_CONF, $_CONF_GEO, $_TABLES;

    require_once __DIR__ . '/install_defaults.php';

    // Get the group ID that was saved previously.
    $group_id = (int)DB_getItem($_TABLES['groups'], 'grp_id',
            "grp_name='{$_CONF_GEO['pi_name']} Admin'");

    return plugin_initconfig_locator($group_id);
}
