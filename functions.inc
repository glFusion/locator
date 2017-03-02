<?php
/**
*   glFusion API functions for the Locator plugin.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2014 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.1.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

/**
*   Global plugin-specific configuration items
*   @global array $_CONF_GEO
*/
global $_CONF_GEO;

//  Check and see if we need to load the plugin configuration
if (!isset($_CONF_GEO['default_radius'])) {
    /** Include the glFusion configuration class if not done already */
    require_once $_CONF['path_system'] . 'classes/config.class.php';

    $geo_config = config::get_instance();
    $_CONF_GEO = $geo_config->get_config('locator');
}
$_CONF_GEO['_is_uikit'] = $_SYSTEM['framework'] == 'uikit' ? true : false;

define('LOCATOR_PI_PATH', $_CONF['path'] . 'plugins/locator');
define('LOCATOR_URL', $_CONF['site_url'] . '/locator');
define('LOCATOR_ADMIN_URL', $_CONF['site_admin_url'] . '/plugins/locator');
define('LOCATOR_CLASSPATH', LOCATOR_PI_PATH . '/classes');

require_once LOCATOR_PI_PATH . '/locator.php';

/** Include local configuration overrides */
if (file_exists(LOCATOR_PI_PATH . '/config.php')) {
    include_once LOCATOR_PI_PATH . '/config.php';
}


/**
* Language file Include
*/
$langpath = LOCATOR_PI_PATH . '/language';
$langfile = "{$_CONF['language']}.php";
if (!file_exists("$langpath/$langfile")) {
    $langfile = 'english.php';
}
include_once "$langpath/$langfile";
if (file_exists("$langpath/custom/$langfile")) {
    include_once "$langpath/custom/$langfile";
}

function USES_locator_functions()
{   require_once LOCATOR_PI_PATH . '/locator_functions.inc.php';    }

function USES_locator_editmarker()
{   require_once LOCATOR_PI_PATH . '/editmarker.php';   }

function USES_locator_upgrade()
{   require_once LOCATOR_PI_PATH . '/upgrade.inc.php';  }

/** Include the user location class */
function USES_locator_class_userloc()
{   require_once LOCATOR_CLASSPATH . '/userloc.class.php';  }

/** Include the user location class */
function USES_locator_class_marker()
{   require_once LOCATOR_CLASSPATH . '/marker.class.php';  }

function USES_locator_google_lang()
{   require_once LOCATOR_PI_PATH . '/google_lang.inc.php';  }


/**
*   Returns the items for this plugin that should appear on the main menu
*
*   @return array   Associative array of menu items to be added to menu
*/
function plugin_getmenuitems_locator()
{
    global $LANG_GEO, $_CONF_GEO;

    $menuitems = '';
    if ($_CONF_GEO['usermenu_option'] == 1 &&
            SEC_hasRights($_CONF_GEO['pi_name'].'.view')) {
        $menuitems = array(
            $LANG_GEO['menulabel'] => LOCATOR_URL . '/index.php',
        );
    }

    return $menuitems;
}


/**
*   Add the admin option to command & control.
*
*   @return   array   Array (plugin name, admin url, url of plugin icon)
*/
function plugin_cclabel_locator()
{
    global $LANG_GEO;

    if (plugin_ismoderator_locator()) {
        return array(
            $LANG_GEO['plugin_name'],
            LOCATOR_ADMIN_URL . '/index.php',
            plugin_geticon_locator(),
        );
    } else {
        return false;
    }
}


/**
*   Returns the administrative menu option for this plugin.
*
*   @return array   Array (plugin name, admin url, # of plugin items or '')
*/
function plugin_getadminoption_locator()
{
    global $_CONF, $LANG_GEO, $_CONF_GEO;

    if (plugin_ismoderator_locator()) {
        return array(
            $LANG_GEO['admin_menu'], 
            LOCATOR_ADMIN_URL . '/index.php', 
            ''
        );
    } else {
        return false;
    }

}


/**
*   Returns the user menu option for this plugin.
*
*   @return array   Array containing (plugin name, plugin user url, 0)
*/
function plugin_getuseroption_locator()
{
    return false;
}


/**
*   Return the current version of code. 
*   Used in the Plugin Editor to show the registered version and code version
*
*   @return string Plugin version
*/
function plugin_chkVersion_locator()
{
    global $_CONF_GEO;

    return $_CONF_GEO['pi_version'];
}


/**
*   Removes the datastructures for this plugin from the glFusion database.
*   This routine will get called from the Plugin install program if user
*   selects De-Install or if Delete is used in the Plugin Editor.
*   The Plugin Installer will also call this routine upon and install error
*   to remove anything it has created.
*   The Plugin installer will pass the optional parameter which will then
*   double check that plugin has first been disabled.
*
*   Returns True if all Plugin related data is removed without error
*
*   @param  string  $installCheck   Defult is blank but if set, check if plugin is disabled first
*   @return boolean                 True if successful false otherwise
*/
function plugin_uninstall_locator($installCheck='')
{
    global $_TABLES, $LANG_PHOTO;

    $pi_name='locator';
    $FEATURES = array (
        'locator.admin',
        'locator.view',
        'locator.submit',
    );
    $TABLES = array (
        'locator_markers',
        'locator_userXorigin',
        'locator_submission',
        'locator_userloc',
    );

    // Ok to proceed and delete plugin

    // Unregister the plugin with glFusion
    COM_errorLog('Attempting to unregister the ' . $pi_name . ' plugin from glFusion',1);
    DB_delete($_TABLES['plugins'], 'pi_name', $pi_name);

    // Drop locator tables
    foreach($TABLES as $table) {
        COM_errorLog("Removing Table {$_TABLES[$table]}",1);
        DB_query("DROP TABLE {$_TABLES[$table]}",1);
    }

    // Remove Security for this plugin
    foreach ($FEATURES as $feature) {
        COM_errorLog("Removing elements of feature $feature");
        $grp_id = (int)DB_getItem($_TABLES['vars'], 'value', "name = 'locator_gid'");
        if ($grp_id > 0) {
            COM_errorLog("Removing $pi_name Admin Group", 1);
            DB_delete($_TABLES['groups'], 'grp_id', $grp_id);
            DB_delete($_TABLES['vars'], 'name', 'locator_gid');
            COM_errorLog("Removing root users from admin of $pi_name");
            DB_delete($_TABLES['group_assignments'], 'ug_main_grp_id', $grp_id);
        }
        // Remove all the associated features -- access rights
        COM_errorLog("Removing $feature feature and rights to it",1);
        $feat_id = (int)DB_getItem($_TABLES['features'], 'ft_id', "ft_name = '$feature'");
        if ($feat_id > 0) {
            DB_delete($_TABLES['access'], 'acc_ft_id', $feat_id);
            DB_delete($_TABLES['features'], 'ft_id', $feat_id);
        }
    }
    COM_errorLog('...success',1);
    return true;
}


/**
*   Gets an array of items to be removed during plugin removal.
*
*   @return array Array of plugin elements to remove
*/
function plugin_autouninstall_locator()
{
    global $_CONF_GEO;

    $out = array (
        'tables'    => array($_CONF_GEO['pi_name']. '_markers',
                            $_CONF_GEO['pi_name']. '_submission',
                            $_CONF_GEO['pi_name']. '_userXorigin',
                            $_CONF_GEO['pi_name']. '_userloc',
                        ),
        'groups'    => array($_CONF_GEO['pi_name'] . ' Admin'),
        'features'  => array($_CONF_GEO['pi_name'] . '.admin', 
                            $_CONF_GEO['pi_name'] . '.submit',
                            $_CONF_GEO['pi_name'] . '.view',
                        ),
        'php_blocks' => array(),
        'vars'      => array($_CONF_GEO['pi_name'] . '_gid')
    );

    return $out;

}


/**
*   Returns the current user's ability to moderate the plugin
*
*   Checks that the current user has the rights to moderate the
*   plugin, returns true if this is the case, false otherwise
*
*   @return boolean True if moderator
*/
function plugin_ismoderator_locator() 
{
    global $_CONF_GEO;
    static $isMod = NULL;
    if ($isMod === NULL) {
        $isMod = SEC_hasRights($_CONF_GEO['pi_name'] . '.admin');
    }
    return $isMod;
}



/**
*   Returns the plugins submission form
*   Returns a string contain the HTML of the submission form for the plugin.
*
*   @return string  The HTML form
*/
function plugin_submit_locator() 
{
    global $HTTP_POST_VARS, $LANG12, $_CONF, $_CONF_GEO, $LANG_GEO;

    // only valid users allowed
    if (!GEO_canSubmit()) {
        return GEO_errorMsg($LANG_GEO['access_denied_msg1'], 'alert', 
                $LANG_GEO['access_denied_msg']);
    }

    // If mode = preview or editsubmission, load the ad info.
    if ($HTTP_POST_VARS['mode'] == $LANG12[32] ||
        $HTTP_POST_VARS['mode'] == 'editsubmission') {
        $A = $HTTP_POST_VARS;
    } else {
        $A = array();
    }

    USES_locator_class_marker();
    $M = new Marker();
    return $M->Edit();
}


/**
*   Returns the URL of the plugin's icon.
*
*   @return string  URL of the icon
*/
function plugin_geticon_locator()
{
    return LOCATOR_URL . '/images/locator.png';
}


/**
*   Search the plugin data
*/
function plugin_dopluginsearch_locator(
    $query,
    $datestart,
    $dateend,
    $topic,
    $type,
    $author,
    $keyType,
    $page,
    $perpage)
{
    global $_CONF, $_TABLES, $LANG_GEO, $_CONF_GEO;

    if (COM_isAnonUser() &&
    (($_CONF['loginrequired'] == 1) ||
    ($_CONF_GEO['loginrequired'] == 1))) {
        return '';
    }

    // Encode query for inclusion in result's url
    $htmlquery = urlencode($query);

    $query = trim(DB_escapeString(htmlspecialchars($query)));

    // Start the SQL query by selecting desired fields.
    $sql = "
        SELECT
            a.id as id, a.title as title,
            a.description as description, a.owner_id, a.views as hits,
            a.add_date AS date,
            CONCAT('/{$_CONF_GEO['pi_name']}/index.php?detail=x&id=',a.id) as url
        FROM
            {$_TABLES['locator_markers']} a
        WHERE
            1=1
    " . COM_getPermSQL('AND', 0, 2, 'a'). ' ';

    if (!empty($author)) {
        $sql .= " AND (a.owner_id = '$author') ";
    }

    $search = new SearchCriteria($_CONF_GEO['pi_name'], $LANG_GEO['menulabel']);
    $columns = array('a.title', 'a.description', 'a.address', 'a.keywords', 'a.url');
    list($sql, $ftsql) = $search->buildSearchSQL($keyType, $query, $columns, $sql);
    //echo $sql;die;
    $search->setSQL($sql);
    //$search->setRank(5);
    //$search->setURLRewrite(true);

    return $search;

}


/**
*   Returns the plugin menu option for searching
*
*   @return array Plugin name => Menu Label
*/
function plugin_searchtypes_locator()
{
    global $LANG_GEO, $_CONF, $_CONF_GEO;

    if (COM_isAnonUser() &&
    (($_CONF['loginrequired'] == 1) ||
    ($_CONF_GEO['loginrequired'] == 1))) {
        return '';
    }

    $tmp[$_CONF_GEO['pi_name']] = $LANG_GEO['menulabel'];

    return $tmp;
}


/**
*   Returns the number of submissions waiting moderation
*   Calculates the number of submissions that are currently waiting
*   to be moderated and returns that number.
*
*   @return      int       number of submissions awaiting moderation
*/
function plugin_submissioncount_locator()
{
    global $_TABLES;

    return DB_count($_TABLES['locator_submission']);
}


/**
*   Show items for moderation on submissions page
*   Uses the Plugin class to return data required by moderation.php to list
*   plugin objects that need to be moderated.
*
*   @return       Plugin       return Plugin class containing submission data
*/
function plugin_itemlist_locator()
{
    global $_TABLES, $LANG_GEO;

    if (plugin_ismoderator_locator()) {
        $plugin = new Plugin();
        $plugin->submissionlabel = $LANG_GEO['menulabel'];
        $plugin->getsubmissionssql = "SELECT id, title, address, 'n/a', owner_id as uid FROM "
                                   . $_TABLES['locator_submission'];
        $plugin->addSubmissionHeading($LANG_GEO['title']);
        $plugin->addSubmissionHeading($LANG_GEO['address']);
        return $plugin;
    }
}


/**
*   Returns list of moderation values
*
*   The array returned contains (in order): the row 'id' label, main plugin
*   table, moderation fields (comma seperated), and plugin submission table
*
*   @return       array        Returns array of useful moderation values
*/
function plugin_moderationvalues_locator()
{
    global $_TABLES;

    return array(
        'id',
        $_TABLES['locator_markers'],
        'id, owner_id, title, address, description, lat, lng, ' .
            'is_origin, keywords, url, add_date, group_id, ' .
            'perm_owner, perm_group, perm_members, perm_anon, enabled',
        $_TABLES['locator_submission']);
}


/**
*   Get plugin-specific Javascript into the header
*
*   @return array   Array of filenames to be included
*/
function plugin_getheaderjs_locator()
{
    $scripts = array(
        LOCATOR_PI_PATH . '/js/toggleEnabled.js',
    );
    return $scripts;
}


/**
*   Get plugin-specific CSS into the header
*
*   @return array   Array of filenames to be included
*/
function plugin_getheadercss_locator()
{
    $styles = array(
        LOCATOR_PI_PATH . '/css/style.css',
    );
    return $styles;
}


/**
*   Use the system message block to display an error message
*   @param  string  $msg    Message to display
*   @param  string  $type   Type or severity of message, default 'info'
*   @param  string  $hdr    Header text, default 'Error'
*   @return string  HTML code to display teh message box
*/
function GEO_errorMsg($msg='', $type='info', $hdr='')
{
    if ($msg == '')
        return '';

    switch ($type) {
        case 'alert':
            $defhdr = 'Alert';
            $class = 'alert';
            break;
        case 'note':
            $defhdr = 'Note';
            $class = 'note';
            break;
        case 'info':
        default:
            $defhdr = 'Information';
            $class = 'info';
            break;
    }

    if ($hdr == '')
        $hdr = $defhdr;

    $display = "<span class=\"$class\">";
    $display .= COM_startBlock($hdr);
    $display .= $msg;
    $display .= COM_endBlock();
    $display .= "</span>";
    return $display;
}


/**
*   Upgrade the plugin
*   Calls locator_do_upgrade() to perform the upgrade if not already
*   at the current revision.
*
*   @return boolean True on success, error code on failure
*/
function plugin_upgrade_locator() 
{
    require_once dirname(__FILE__) . '/upgrade.inc.php';
    return locator_do_upgrade();
}


/**
*   Select default group for ad access (viewing and editing)
*
*   @uses   GEO_getGroups()
*   @return array   Array of groups for the selection dropdown
*/
function plugin_configmanager_select_defgrp_locator()
{
    return GEO_getGroups('\'All Users\'', 'NOT');
}


/**
*   Get an array of groups.
*   A comma-separated list of groups can be included or excluded.
*
*   @param string $grplist  Comma-separated list of groups, SQL format
*   @param string $NOT      Set to "NOT" to exclude $grplist, blank to include.
*   @return array           Array of groups.
*/
function GEO_getGroups($grplist='', $NOT='')
{
    global $_TABLES;

    $A = array();

    $query = "SELECT grp_id, grp_name FROM {$_TABLES['groups']}";

    // If a group list is specified, add it as a condition with the
    // specified keyword.
    if ($grplist!= '') {
        $query .= " WHERE grp_name $NOT IN ($grplist)";
    }

    $result = DB_query($query);
    if ($result && DB_numRows($result) > 0) {
        while ($row = DB_fetchArray($result)) {
            $A[$row['grp_name']] = $row['grp_id'];
        }
    }

    return $A;

}


/**
*   Send an email to the administrator upon a new submission.
*
*   @param  array   $A  Array of form or database fields
*   @return boolean True on success
*/
function GEO_sendAdminNotification($A)
{
    global $LANG_DIRECTION, $LANG_CHARSET, $_CONF, $_TABLES, 
            $LANG_GEO, $_CONF_GEO;

    require_once $_CONF['path'].'lib/phpmailer/class.phpmailer.php';

    if (empty($LANG_DIRECTION)) {
        // default to left-to-right
        $direction = 'ltr';
    } else {
        $direction = $LANG_DIRECTION;
    }
    if( empty( $LANG_CHARSET )) {
        $charset = $_CONF['default_charset'];
        if( empty( $charset )) {
            $charset = 'iso-8859-1';
        }
    } else {
        $charset = $LANG_CHARSET;
    }

    COM_clearSpeedlimit(300, $_CONF_GEO['pi_name'] . 'notify');
    $last = COM_checkSpeedlimit ($_CONF_GEO['pi_name'] . 'notify');
    if ( $last == 0 ) {
        $mail = new PHPMailer();
        $mail->CharSet = $charset; 
        if ($_CONF['mail_backend'] == 'smtp' ) {
            $mail->Host     = $_CONF['mail_smtp_host'] . ':' . $_CONF['mail_smtp_port'];
            $mail->SMTPAuth = $_CONF['mail_smtp_auth'];
            $mail->Username = $_CONF['mail_smtp_username'];
            $mail->Password = $_CONF['mail_smtp_password'];
            $mail->Mailer = "smtp";
        } elseif ($_CONF['mail_backend'] == 'sendmail') {
            $mail->Mailer = "sendmail";
            $mail->Sendmail = $_CONF['mail_sendmail_path'];
        } else {
            $mail->Mailer = "mail";
        }
        $mail->WordWrap = 76;
        $mail->IsHTML(false);

        $title = $A['title'];
        $address = $A['address'];

        $mail->Subject = $_CONF['site_name'] . ' ' . $LANG_GEO['new_submission'];

        $mailbody = $LANG_GEO['have_new_submission']. "\n\n";
        $mailbody .= $LANG_GEO['title']. ": " . $summary . "\n";
        $mailbody .= $LANG_GEO['address']. ": " . $address. "\n\n";
        $mailbody .= "{$LANG_GEO['click_to_moderate']}<{$_CONF['site_admin_url']}/moderation.php>\n\n";

        $mail->From     = $_CONF['site_mail'];
        $mail->FromName = $_CONF['site_name'];
        $mail->Body     = $mailbody;

        $group_id = DB_getItem($_TABLES['groups'],'grp_id','grp_name="locator Admin"');

        $groups = GEO_getGroupList($group_id);
        $groupList = implode(',',$groups);

        $sql = "SELECT DISTINCT {$_TABLES['users']}.uid,username,fullname,email "
              ."FROM {$_TABLES['group_assignments']},{$_TABLES['users']} "
              ."WHERE {$_TABLES['users']}.uid > 1 "
              ."AND {$_TABLES['users']}.uid = {$_TABLES['group_assignments']}.ug_uid "
              ."AND ({$_TABLES['group_assignments']}.ug_main_grp_id IN ({$groupList}))";

        $result = DB_query($sql);
        $nRows = DB_numRows($result);
        $toCount = 0;
        for ($i=0;$i < $nRows; $i++ ) {
            $row = DB_fetchArray($result);
            if ( $row['email'] != '' ) {
                COM_errorLog("Locator Submit: Sending notification email to: " . $row['email'] . " - " . $row['username']);
                $toCount++;
                $mail->AddAddress($row['email'], $row['username']);
            }
        }
        if ( $toCount > 0 ) {
            if(!$mail->Send()) {
                COM_errorLog("Locator Submit: Unable to send moderation email - error:" . $mail->ErrorInfo);
            }
        } else {
            COM_errorLog("Locator Submit: Error - Did not find any administrators to email");
        }
        COM_updateSpeedlimit($_CONF_GEO['pi_name'].'notify');
    }
    return true;

}


function GEO_getGroupList($basegroup)
{
    global $_TABLES;

    $to_check = array();
    array_push ($to_check, $basegroup);

    $checked = array();

    while (sizeof($to_check) > 0) {
        $thisgroup = array_pop ($to_check);
        if ($thisgroup > 0) {
            $result = DB_query ("SELECT ug_grp_id 
                    FROM {$_TABLES['group_assignments']}
                    WHERE ug_main_grp_id = $thisgroup");
            $numGroups = DB_numRows($result);
            for ($i = 0; $i < $numGroups; $i++) {
                $A = DB_fetchArray ($result);
                if (!in_array ($A['ug_grp_id'], $checked)) {
                    if (!in_array ($A['ug_grp_id'], $to_check)) {
                        array_push ($to_check, $A['ug_grp_id']);
                    }
                }
            }
            $checked[] = $thisgroup;
        }
    }

    return $checked;
}


/**
*   Determines if the current user can submit a location
*
*   @return boolean True if submission allowed, False otherwise
*/
function GEO_canSubmit()
{
    global $_CONF, $_CONF_GEO, $_GROUPS;

    if ((COM_isAnonUser() && $_CONF_GEO['anon_submit'] == false) ||
        (!COM_isAnonUser() && $_CONF_GEO['user_submit'] == false && 
            !in_array($_CONF_GEO['pi_name'] . ' Admin', $_GROUPS))) {
        return false;
    } else {
        return true;
    }
}


/**
*   Implements the [locator:] autotag.
*
*   @param  string  $op         Operation to perform
*   @param  string  $content    Page Content
*   @param  string  $autotag    Autotag parameters
*   @return string              HTML generated by autotag
*/
function plugin_autotags_locator($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES, $_CONF_GEO;

    switch ($op) {
    case 'tagname':
        $retval = 'locator';
        break;
    case 'parse':
        $HTML = '';
        $option = COM_applyFilter($autotag['parm1']);
        switch ($option) {
        case 'map':
            // Insert a google map, if configured correctly.  We check this
            // here just to save time looking up markers if we're not.
            if ($_CONF_GEO['show_map'] == 0) {
                break;
            }

            // Don't insert a map into an e-mailed article.  Doesn't work.
            if (isset($_POST['what']) && $_POST['what'] == 'sendstory') {
                break;
            }

            $parm2 = $autotag['parm2'];
            list($opt, $parm3) = explode(':', $parm2);
            if (empty($parm3)) {
                $opt = 'id';
                $parm3 = $parm2;
            }

            switch($opt) {
            case 'id':
                $A = DB_fetchArray(DB_query(
                    "SELECT lat, lng, title
                    FROM {$_TABLES['locator_markers']}
                    WHERE id='" . DB_escapeString($parm3) . "'", 1), false);
                if (!empty($A)) {
                    $lat = $A['lat'];
                    $lng = $A['lng'];
                    $title = $A['title'];
                }
                break;

            case 'addr':
            case 'address':
                if (!empty($parm3)) {
                    USES_locator_class_userloc();
                    $U = new UserLoc($parm3, 1);
                    $lat = $U->lat;
                    $lng = $U->lng;
                    $title = $parm3;
                }
                break;
            }
            if (!empty($lat) && !empty($lng)) {
                $HTML = GEO_showMap($lat, $lng, $title);
            }
            break;

        } // switch($option)

        $retval = str_replace($autotag['tagstr'], $HTML, $content);
        break;

    default:
        $retval = $content;
        break;

    }   // switch ($op)

    return $retval;

}


/**
*   Perform scheduled functions, such as deleting user-entered locations
*/
function plugin_runScheduledTask_locator()
{
    global $_TABLES, $_CONF_GEO;

    // Purge user-entered locations if enabled.
    // Only user-entered origin strings (type 1) are deleted.  Origins
    // associated with the location in the user's account settings are
    // left alone.
    $purge_days = (int)$_CONF_GEO['purge_userlocs'];
    if ($purge_days > 0) {
        $purge_exp_date = time() - ($purge_days * 86400);
        DB_query("DELETE FROM {$_TABLES['locator_userloc']}
                WHERE type = 1 
                AND add_date < DATE_SUB(NOW(), INTERVAL $purge_days DAY)
        ");

    }

}


/**
*   Create a basic map for display.
*
*   @param  float   $lat    Latitude
*   @param  float   $lng    Longitude
*   @param  string  $text   Optional text for marker popup
*   @return string          HTML code for map display
*/
function GEO_showMap($lat, $lng, $text='')
{
    global $_CONF_GEO, $_CONF;

    // Insert a google map, if configured correctly
    if ($_CONF_GEO['show_map'] == 0) {
        return '';
    }

    $lat = (float)$lat;
    $lng = (float)$lng;
    if ($lat == 0 || $lng == 0)
        return '';

    USES_locator_google_lang();
    $iso_lang = is_array($GOOG_lang) && 
                in_array($_CONF['iso_lang'], $GOOG_lang) ? 
                $_CONF['iso_lang'] : 'en';

    list($js_url, $canvas_id) = GEO_MapJS();
    $T = new Template(LOCATOR_PI_PATH . '/templates');
    $T->set_file('page', 'map.thtml');
    $T->set_var(array(
        'lat'           => number_format($lat, 8, '.', ''),
        'lng'           => number_format($lng, 8, '.', ''),
        'infowindow_text' => COM_checkHTML($text),
        'iso_lang'      => $iso_lang,
        'geo_map_js_url' => $js_url,
        'canvas_id'     => $canvas_id,
    ) );
    $T->parse('output','page');
    return $T->finish($T->get_var('output'));
}


/**
 *  Displays a map in the user's profile.
 *
 *  @param  integer $uid    User ID whose profile is being displayed
 *  @param  object  &$T     Pointer to template object
 *  @return string          HTML to include in profile page
 */
function plugin_profilevariablesdisplay_locator($uid, &$T)
{
    global $_CONF_GEO, $_TABLES;

    $retval = '';

    // Make sure that a valid UID was passed in and that we are supposed
    // to show a map in the profile.
    if (!isset($_CONF_GEO['profile_showmap']) ||
        $_CONF_GEO['profile_showmap'] == 0 ||
        $uid < 2) {
        return '';
    }

    // Get the user's location if possible.  If none available, return
    $userloc = DB_getItem($_TABLES['userinfo'], 'location', "uid=$uid");
    if ($userloc == '') {
        return '';
    }

    USES_locator_class_userloc();
    $U = new UserLoc($userloc);

    $LT = new Template(LOCATOR_PI_PATH . '/templates');
    $LT->set_file('page', 'profile_map.thtml');
    $LT->set_var(array(
        'profile_map'   => GEO_showMap($U->lat, $U->lng, $userloc),
    ) );
    $LT->parse('output','page');
    $existingFields = $T->get_var('plugin_fields');
    $T->set_var('plugin_fields', 
        $existingFields . $LT->finish($LT->get_var('output'))
    );
 
}


/**
*   Get the link to a documentation file.
*   Returns the URL to the file, or to the directory if no file requested.
*
*   @param  string  $file       HTML file
*   @param  string  $doclang    Documentation language
*   @return string              URL to document directory or file
*/
function LOCATOR_getDocURL($file='', $doclang='english')
{
    $doc_path = $_CONF['path_html'] . $_CONF_LOCATOR['pi_name'] . 
                "/docs/$doclang/";
    $doc_url = '';

    if ($file != '') {
        if (!file_exists($doc_path . $file)) {
            $doclang = 'english';
        }
        $doc_url = LOCATOR_URL . "/docs/$doclang/$file";
    } else {
        if (!is_dir($doc_path)) {
            $doclang = 'english';
        }
        $doc_url = LOCATOR_URL . "/docs/$doclang/";
    }

    return $doc_url;
}

    

/**
*   Get the URL to the localized configuration help file.
*
*   @uses   LOCATOR_getDocURL()
*   @param  string  $option     Configuration option to display
*   @param  string  $doclang    Documentation language requested
*   @return array   Array of (url, window_type)
*/
function plugin_getconfigelementhelp_locator($option, $doclang='english')
{
    if (version_compare(GVERSION, '1.6.0', '<')) {
        $file = 'config.legacy.html';
    } else {
        $file = 'config.html';
    }
    $doc_url = LOCATOR_getDocURL($file, $doclang);
    if (empty($doc_url))
        return false;

    if (!empty($option)) {
        $doc_url .= '#desc_' . $option;
    }

    return array($doc_url, 0);
}


/**
*   Looks up coordinates from Google for the given address.
*
*   @uses   GEO_file_get_contents()
*   @param  string  $address    Address to look up
*   @param  float   &$lat       Reference to latitude value
*   @param  float   &$lng       Reference to longitude value
*   @return int                 Zero for success, or -1 for failure
*/
function GEO_getCoords($address, &$lat, &$lng)
{
    global $_CONF_GEO;

    if ($_CONF_GEO['autofill_coord'] != 1)
        return 0;

    $address = urlencode($address);
    $url = GEO_GOOG_URL . $address . '&key=' . $_CONF_GEO['google_api_key'];

    $json = GEO_file_get_contents($url);
    if ($json == '') {
        return 0;
    }
    $data = json_decode($json, true);
    if (!is_array($data) || $data['status'] != 'OK') {
        return -1;
    }
    $lat = $data['results'][0]['geometry']['location']['lat'];
    $lng = $data['results'][0]['geometry']['location']['lng'];

    return 0;
}


/**
*   Returns an array containing the data for a single location.
*   Used as an API function to allow other plugins to look up a location.
*   The returned array will have all items, plus address components.
*
*   @since  1.0.3
*   @param  string  $id         Location ID to retrieve
*   @param  string  $singleAddress  True to return a single address string,
*                                   False to return address components
*   @return array       Array of location information, or false on failure
*/
function GEO_getInfo($id, $singleAddress = false)
{
    global $_TABLES;

    // A "select *" is done since we don't know what the caller might want
    $res = DB_query("SELECT *
                FROM {$_TABLES['locator_markers']}
                WHERE id='" . COM_sanitizeId($id) . "' 
                AND enabled = 1 " . 
                COM_getPermSQL('AND'));
    $A = DB_fetchArray($res, false);
    if (empty($A)) {
        return false;   // Empty result, DB error, whatever.
    }

    if (!$singleAddress) {
        // Try to extract the address string into component parts.
        // Obviously this is just a guess.
        $Addr = explode(',', $A['address']);
        $A['street'] = trim($Addr[0]);
        $A['city'] = trim($Addr[1]);
        $A['province'] = trim($Addr[2]);
        $A['postal'] = trim($Addr[3]);
    }

    return $A;
}


/**
*   Get the URL to Google Maps for inclusion in a template
*   This makes sure the javascript is included only once even if there
*   are multiple maps on the page.
*   Returns the URL, and a random number to be used for the canvas ID.
*
*   @return array   $url=>URL to Google Maps javascript, $canvas_id=> random ID
*/
function GEO_MapJS()
{
    global $_CONF_GEO;

    $canvas_id = rand(1,999);   // Create a random id for the canvas
    if (!defined('GEO_HAVE_MAP_JS')) {
        define('GEO_HAVE_MAP_JS', 1);
        $url = '<script src="' .
            sprintf(GEO_MAP_URL, $_CONF_GEO['google_api_key']) .
            '"></script>';
    } else {
        $url = '';
    }

    return array($url, $canvas_id);
}


/**
*   Retrieve the contents of a remote URL into a file.
*   Emulates file_get_contents(), but falls back to curl if the
*   former isn't allowed.
*
*   @param  string  $url    URL to retrieve
*   @return string          Raw contents from URL
*/
function GEO_file_get_contents($url)
{
    if (ini_get('allow_url_fopen') == 1) {

        $result = file_get_contents($url, 0);

    } elseif (in_array('curl', get_loaded_extensions())) {

        $agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; ' .
                'rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR ' .
                '3.5.30729)';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_USERAGENT,      $agent);
        curl_setopt($ch, CURLOPT_HEADER,         0);
        curl_setopt($ch, CURLOPT_ENCODING,       "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR,    1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT,        8);

        $result = curl_exec($ch);
        curl_close($ch);

    } else {

        $result = '';
        COM_errorLog('LOCATOR: Missing url_fopen and curl support');

    }

    return $result;
}


/**
*   Return true since this plugin supports webservices
*
*   @return boolean     False, web services are not supported
*/
function plugin_wsEnabled_locator()
{
    return false;
}


/**
*   Support merging local accounts into remote accounts
*
*   @param  integer $origUID    Original (remote) user ID
*   @param  integer $destUID    Merged (local) user ID
*/
function plugin_user_move_locator($origUID, $destUID)
{
    global $_TABLES;

    $origUID = (int)$origUID;
    $destUID = (int)$destUID;

    DB_query("UPDATE {$_TABLES['locator_markers']}
            SET owner_id = $destUID WHERE owner_id = $origUID", 1);
    DB_query("UPDATE {$_TABLES['locator_submission']}
            SET owner_id = $destUID WHERE owner_id = $origUID", 1);
    DB_query("UPDATE {$_TABLES['locator_userXloc']}
            SET uid = $destUID WHERE uid = $origUID", 1);
}


/**
*   Get a map given a pair of coordinates
*
*   @param  array   $args       Array of item information
*   @param  array   &$output    Pointer to output array
*   @param  array   &$svc_msg   Unused
*   @return integer             Status code
*/
function service_getMap_locator($args, &$output, &$svc_msg)
{
    if (!is_array($args)) return PLG_RET_ERROR;
    $lat = isset($args['lat']) ? (float)$args['lat'] : 0;
    $lng = isset($args['lng']) ? (float)$args['lng'] : 0;
    $output = GEO_showMap($lat, $lng);
    return PLG_RET_OK;
} 


/**
*   Get the coordinates for a given address
*
*   @param  array   $args       Array of item information
*   @param  array   &$output    Pointer to output array
*   @param  array   &$svc_msg   Unused
*   @return integer             Status code
*/
function service_getCoords_locator($args, &$output, &$svc_msg)
{
    //if (!is_array($args) || empty($args['address'])) return PLG_RET_ERROR;
    $output['lat'] = 0;
    $output['lng'] = 0;
    if (GEO_getCoords($args, $output['lat'], $output['lng']) == 0) {
        return PLG_RET_OK;
    } else {
        return PLG_RET_ERROR;
    }
}
 

/**
*   Returns a selection list of locations.
*   Intended for other plugins to create a dropdown list.
*
*   @uses   COM_optionList()
*   @param  string  $sel        Currently selected item ID
*   @return string          Options to be bracketd by Select tags
*/
function GEO_optionList($sel='')
{
    global $_TABLES;

    return COM_optionList($_TABLES['locator_markers'], 'id,title', $sel);
}


/**
*   Returns a selection list of locations.
*   Intended for other plugins to create a dropdown list.
*
*   @uses   GEO_optionList()
*   @param  array   $args       Unused
*   @param  string  &$output    String to hold resulting option list
*   @param  string  &$svc_msg   String to hold any service message
*   @return integer             Just returns OK, $output may be empty at worst
*/
function service_optionList_locator($args, &$output, &$svc_msg)
{
    global $_TABLES;

    $sel = isset($args['selected']) ? $args['selected'] : '';
    $output = GEO_optionList($sel);
    return PLG_RET_OK;
}


/**
*   Return general information about a location
*
*   @uses   GEO_getInfo()
*   @param  array   $args       Array of parameters
*   @param  array   &$output    Array to receive information items
*   @param  string  &$svc_msg   String to hold any service messages
*   @return integer             Status of function call
*/
function service_getInfo_locator($args, &$output, &$svc_msg)
{
    $id = $args['id'];
    $single = isset($args['single']) && $args['single'] ? true : false;
    $output = GEO_getInfo($id, $single);
    if ($output !== false)
        return PLG_RET_OK;
    else
        return PLG_RET_ERROR;
}


/**
*   Invoke a service in another plugin.
*   This is our own version of PLG_invokeService() so that other plugins
*   don't have to enable web services.
*
*   Service function names are formatted as "service_SERVICENAME_PLUGIN()"
*
*   @param  string  $type       Plugin name (PLUGIN)
*   @param  string  $action     Plugin service (SERVICENAME)
*   @param  array   $args       Array of arguments to pass to the plugin
*   @param  mixed   &$output    Pointer to output values
*   @param  mixed   &$svc_msg   Pointer to service message
*   @return integer Return value, see lib-plugins.php
*/
function GEO_invokeService($type, $action, $args, &$output, &$svc_msg)
{
    global $_CONF;

    $retval = PLG_RET_ERROR;

    $output  = '';
    $svc_msg = '';

    // Check if the plugin type and action are valid
    $function = 'service_' . $action . '_' . $type;
    if (function_exists($function)) {
        if (!isset($args['gl_svc'])) {
            $args['gl_svc'] = false;
        }
        $retval = $function($args, $output, $svc_msg);
    }

    return $retval;
}

?>
