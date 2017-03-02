<?php
/**
*   Upgrade the plugin
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2016 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.1.0
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

global $_CONF, $_CONF_GEO, $_DB_dbms;

/** Include default values for new config items */
require_once "{$_CONF['path']}plugins/{$_CONF_GEO['pi_name']}/install_defaults.php";

/**
*   Sequentially perform version upgrades.
*   @param  string  $current_ver    Existing installed version to be upgraded
*   @return boolean     True on success, False on failure
*/
function locator_do_upgrade($current_ver)
{
    global $_CONF_GEO, $_PLUGIN_INFO;

    if (isset($_PLUGIN_INFO[$_CONF_GEO['pi_name']])) {
        $current_version = $_PLUGIN_INFO[$_CONF_GEO['pi_name']];
    } else {
        return false;
    }

    if (!COM_checkVersion($current_ver, '0.1.1')) {
        if (!locator_upgrade_0_1_1()) return false;
        $current_ver = '0.1.1';
    }

    if (!COM_checkVersion($current_ver, '0.1.4')) {
        if (!locator_upgrade_0_1_4()) return false;
        $current_ver = '0.1.4';
    }

    if (!COM_checkVersion($current_ver, '1.0.1')) {
        if (!locator_upgrade_1_0_1()) return false;
        $current_ver = '1.0.1';
    }

    if (!COM_checkVersion($current_ver, '1.0.2')) {
        if (!locator_upgrade_1_0_2()) return false;
        $current_ver = '1.0.2';
    }

    if (!COM_checkVersion($current_ver, '1.1.0')) {
        if (!locator_upgrade_1_1_0()) return false;
        $current_ver = '1.1.0';
    }

    return true;
}


/**
*   Execute the SQL statement to perform a version upgrade.
*   An empty SQL parameter will return success.
*
*   @param string   $version  Version being upgraded to
*   @param array    $sql      SQL statement to execute
*   @return boolean     True on success, False on failure
*/
function locator_do_upgrade_sql($version, $sql='')
{
    global $_TABLES, $_CONF_GEO;

    // If no sql statements passed in, return success, this could be normal
    if (!is_array($sql))
        return true;

    // Execute SQL now to perform the upgrade
    COM_errorLOG("--Updating {$_CONF_GEO['pi_display_name']} to version $version");
    foreach ($sql as $s) {
        COM_errorLOG("{$_CONF_GEO['pi_display_name']} $version update: Executing SQL => $s")
        DB_query($s,'1');
        if (DB_error()) {
            COM_errorLog("SQL Error during {$_CONF_GEO['pi_display_name']} plugin update",1);
            return false;
        }
    }
    return true;
}


/**
*   Update the plugin version number in the database.
*   Called at each version upgrade to keep up to date with
*   successful upgrades.
*
*   @param  string  $ver    New version to set
*   @return boolean         True on success, False on failure
*/
function locator_do_set_version($ver)
{
    global $_TABLES, $_CONF_GEO;

    // now update the current version number.
    $sql = "UPDATE {$_TABLES['plugins']} SET
            pi_version = '{$_CONF_GEO['pi_version']}',
            pi_gl_version = '{$_CONF_GEO['gl_version']}',
            pi_homepage = '{$_CONF_GEO['pi_url']}'
        WHERE pi_name = '{$_CONF_GEO['pi_name']}'";

    $res = DB_query($sql, 1);
    if (DB_error()) {
        COM_errorLog("Error updating the {$_CONF_GEO['pi_display_name']} Plugin version",1);
        return false;
    } else {
        COM_errorLog("Succesfully updated the {$_CONF_GEO['pi_display_name']} Plugin",1);
        return true;
    }
}


/**
*   Upgrade to version 0.1.1
*   @return integer Result from locator_do_upgrade_sql()
*/
function locator_upgrade_0_1_1()
{
    global $_TABLES, $_CONF_GEO, $_GEO_DEFAULT;

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_GEO['pi_name'])) {
        $c->add('show_right_blk', $_GEO_DEFAULT['show_right_blk'],
               'select', 0, 0, 3, 70, true, $_CONF_GEO['pi_name']);
    }

    return locator_do_set_version('0.1.1');
}

/**
*   Upgrade to version 0.1.4
*   @return integer Result from locator_do_upgrade_sql()
*/
function locator_upgrade_0_1_4()
{
    global $_TABLES, $_CONF_GEO, $_GEO_DEFAULT;

    $c = config::get_instance();
    if ($c->group_exists($_CONF_GEO['pi_name'])) {
        $c->add('purge_userlocs', $_GEO_DEFAULT['purge_userlocs'],
               'text', 0, 0, 3, 70, true, $_CONF_GEO['pi_name']);
    }

    $sql = array(
        "ALTER TABLE {$_TABLES['locator_userloc']}
            ADD type TINYINT(1) DEFAULT 0 AFTER id";
        "ALTER TABLE {$_TABLES['locator_userloc']}
            CHANGE add_date add_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        // new "enabled" field for markers
        "ALTER TABLE {$_TABLES['locator_markers']}
            ADD enabled TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";
    );

    return locator_do_upgrade_sql('0.1.4', $sql) ? locator_do_set_version('0.1.4') : false;
}

/**
*   Upgrade to version 1.0.1
*   @return integer Result from locator_do_upgrade_sql()
*/
function locator_upgrade_1_0_1()
{
    global $_TABLES, $_CONF_GEO, $_GEO_DEFAULT;

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_GEO['pi_name'])) {
        // Remove individual block selections and combine into one.
        // Assume left blocks are shown, since it wasn't an option before.
        $displayblocks = 1;
        if ($_CONF_GEO['show_right_blk']) $displayblocks += 2;

        $c->del('show_right_blk', $_CONF_GEO['pi_name']);
        $c->add('displayblocks', $displayblocks,
                'select', 0, 0, 13, 70, true, $_CONF_GEO['pi_name']);

        $c->add('profile_showmap', $_GEO_DEFAULT['profile_showmap'],
               'select', 0, 0, 3, 80, true, $_CONF_GEO['pi_name']);

        $c->add('usermenu_option', $_GEO_DEFAULT['usermenu_option'],
               'select', 0, 0, 3, 90, true, $_CONF_GEO['pi_name']);

    }

    // Add 'enabled' field to submissions that should have been in 0.1.4
    $sql[] = "ALTER TABLE {$_TABLES['locator_submission']}
        ADD enabled TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";

    return locator_do_upgrade_sql('1.0.1', $sql) ? locator_do_set_version('1.0.1') : false;
}


/**
*   Upgrade to version 1.0.2
*   @return integer Result from locator_do_upgrade_sql()
*/
function locator_upgrade_1_0_2()
{
    global $_CONF_GEO, $_GEO_DEFAULT;

    // Add new configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_GEO['pi_name'])) {
        $c->add('use_weather', $_GEO_DEFAULT['use_weather'],
               'select', 0, 0, 3, 90, true, $_CONF_GEO['pi_name']);
        $c->add('use_directions', $_GEO_DEFAULT['use_directions'],
               'select', 0, 0, 3, 100, true, $_CONF_GEO['pi_name']);
    }

    return locator_do_set_version('1.0.2');
}


/**
*   Upgrade to version 1.1.0
*   @return integer Result from locator_do_upgrade_sql()
*/
function locator_upgrade_1_1_0()
{
    global $_CONF_GEO;

    // Remove deprecated configuration items
    $c = config::get_instance();
    if ($c->group_exists($_CONF_GEO['pi_name'])) {
        $c->del('url_geocode', $_CONF_GEO['pi_name']);
    }

    return locator_do_set_version('1.1.0');
}

?>
