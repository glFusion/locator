<?php
/**
 * Upgrade the plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2018 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     v1.1.4
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

global $_CONF, $_CONF_GEO, $_DB_dbms, $_SQL_UPGRADE;

/** Include SQL upgrades */
require_once dirname(__FILE__) . '/sql/mysql_install.php';

/**
 * Sequentially perform version upgrades.
 * If any step fails, immediately return False.
 *
 * @param   boolean $dvlp   True to ignore errors and continue
 * @return  boolean     True on success, False on failure
 */
function locator_do_upgrade($dvlp=false)
{
    global $_CONF_GEO, $_PLUGIN_INFO, $_TABLES;

    if (isset($_PLUGIN_INFO[$_CONF_GEO['pi_name']])) {
        if (is_array($_PLUGIN_INFO[$_CONF_GEO['pi_name']])) {
            // glFusion > 1.6.5
            $current_ver = $_PLUGIN_INFO[$_CONF_GEO['pi_name']]['pi_version'];
        } else {
            // legacy
            $current_ver = $_PLUGIN_INFO[$_CONF_GEO['pi_name']];
        }
    } else {
        return false;
    }
    $code_ver = plugin_chkVersion_locator();

    $map_provider = NULL;   // change if the map provider must be set

    if (!COM_checkVersion($current_ver, '0.1.1')) {
        $current_ver = '0.1.1';
        if (!locator_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!locator_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.1.4')) {
        $current_ver = '0.1.4';
        if (!locator_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!locator_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '1.0.1')) {
        $current_ver = '1.0.1';
        if (!locator_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!locator_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '1.0.2')) {
        $current_ver = '1.0.2';
        if (!locator_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!locator_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '1.1.0')) {
        $current_ver = '1.1.0';
        if (!locator_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!locator_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '1.1.1')) {
        $current_ver = '1.1.1';
        // Drop this key with error checking off since it may not exist
        DB_query("ALTER TABLE {$_TABLES['locator_userloc']}
                DROP KEY `location`", 1);
        if (!locator_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!locator_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '1.2.0')) {
        $current_ver = '1.2.0';
        // The map provider value should be reset to "google" since that was the
        // only option prior to this version.
        if (!$dvlp) {
            $map_provider = 'google';
        }
        if (!locator_do_upgrade_sql($current_ver, $dvlp)) return false;
        if (!locator_do_set_version($current_ver)) return false;
    }

    // Final version update to catch updates that don't go through
    // any of the update functions, e.g. code-only updates
    if (!COM_checkVersion($current_ver, $code_ver)) {
        if (!locator_do_set_version($code_ver)) {
            return false;
        }
    }

    // Sync any config changes
    USES_lib_install();
    require_once __DIR__ . '/install_defaults.php';
    _update_config('locator', $locatorConfigData);

    // Prior to v1.2.0 the only map provider was Google. If upgrading from
    // a previous version, override the new provider selection.
    if ($map_provider !== NULL) {
        $c = config::get_instance();
        $c->set('mapper', $map_provider, $_CONF_GEO['pi_name']);
        $c->set('geocoder', $map_provider, $_CONF_GEO['pi_name']);
    }

    LOC_remove_old_files();
    COM_errorLog("Successfully updated the {$_CONF_GEO['pi_display_name']} Plugin", 1);
    return true;
}


/**
 * Execute the SQL statement to perform a version upgrade.
 * An empty SQL parameter will return success.
 *
 * @param   string  $version        Version being upgraded to
 * @param   boolean $ignore_errors  True to ignore SQL errors and continue
 * @return  boolean     True on success, False on failure
 */
function locator_do_upgrade_sql($version, $ignore_errors=false)
{
    global $_TABLES, $_CONF_GEO, $_SQL_UPGRADE;

    // If no sql statements to execute, return success. This could be normal.
    if (!isset($_SQL_UPGRADE[$version]) || !is_array($_SQL_UPGRADE[$version]))
        return true;

    // Execute SQL now to perform the upgrade
    COM_errorLOG("--Updating {$_CONF_GEO['pi_display_name']} to version $version");
    foreach ($_SQL_UPGRADE[$version] as $s) {
        COM_errorLOG("{$_CONF_GEO['pi_display_name']} $version update: Executing SQL => $s");
        DB_query($s,'1');
        if (DB_error()) {
            COM_errorLog("SQL Error during {$_CONF_GEO['pi_display_name']} plugin update",1);
            if (!$ignore_errors) return false;
        }
    }
    return true;
}


/**
 * Update the plugin version number in the database.
 * Called at each version upgrade to keep up to date with
 * successful upgrades.
 *
 * @param   string  $ver    New version to set
 * @return  boolean         True on success, False on failure
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
        COM_errorLog("Succesfully updated the {$_CONF_GEO['pi_display_name']} Plugin version",1);
        return true;
    }
}


/**
 * Remove deprecated files
 * Errors in unlink() and rmdir() are ignored.
 */
function LOC_remove_old_files()
{
    global $_CONF;

    $paths = array(
        // private/plugins/locator
        __DIR__ => array(
            'edituserloc.php',
            'google_lang.inc.php',
            'locator_functions.inc.php',
        ),
        // public_html/locator
        $_CONF['path_html'] . 'locator' => array(
        ),
        // admin/plugins/locator
        $_CONF['path_html'] . 'admin/plugins/locator' => array(
        ),
    );
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        // Files that were renamed, changing case only.
        // Only delete these on non-windows systems.
        $files = array(
            // 1.2.1
            'classes/marker.class.php',
            'classes/userloc.class.php',
        );
        $paths[__DIR__] = array_merge($paths[__DIR__], $files);
    }

    foreach ($paths as $path=>$files) {
        foreach ($files as $file) {
            if (is_file("$path/$file")) {
                COM_errorLog("removing $path/$file");
                @unlink("$path/$file");
            }
        }
    }
}

?>
