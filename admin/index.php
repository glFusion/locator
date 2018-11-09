<?php
/**
 * Administrator interface for the Locator plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.0.2
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */

/** Include required glFusion common functions */
require_once '../../../lib-common.php';

/** Include plugin-specific functions */
USES_locator_functions();
/** Include system admin functions */
USES_lib_admin();

/**
 * Create the admin menu block.
 *
 * @param   string  $view   Name of view, for creating New link and help.
 * @return  string  HTML for the menu block
 */
function GEO_adminMenu($view = '')
{
    global $LANG_ADMIN, $LANG_GEO, $_CONF, $_CONF_GEO;

    $retval = '';
    if (!empty($view) && isset($LANG_GEO['menu_hlp'][$view])) {
        $desc_text = $LANG_GEO['menu_hlp'][$view];
    }

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home']),
        array('url' => LOCATOR_ADMIN_URL . '/index.php?edit=x',
              'text' => $LANG_GEO['contrib_origin']),
        array('url' => LOCATOR_ADMIN_URL . '/index.php',
              'text' => $LANG_GEO['manage_locations']),
        array('url' => LOCATOR_ADMIN_URL . '/index.php?mode=userloc',
              'text' => $LANG_GEO['manage_userlocs']),
    );

    $header_str = $LANG_GEO['plugin_name'] . ' ' . $LANG_GEO['version'] . 
        ' ' . $_CONF_GEO['pi_version'];

    $retval .= ADMIN_createMenu($menu_arr, $view, '');
    return $retval;
}


/**
 * Returns a formatted field to the admin list when managing general locations.
 *
 * @param   string  $fieldname  Name of field
 * @param   string  $fieldvalue Value of field
 * @param   array   $A          Array of all values
 * @param   array   $icon_arr   Array of icons
 * @return  string              String to display for the selected field
 */
function plugin_getListField_marker($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_CONF_GEO, $LANG24, $LANG_GEO, $LANG_ADMIN;

    $retval = '';

    switch($fieldname) {
    case 'edit':
        if ($_CONF_GEO['_is_uikit']) {
            $retval = COM_createLink('',
                LOCATOR_ADMIN_URL . '/index.php?edit=x&amp;id=' .$A['id'],
                array(
                    'class' => 'uk-icon uk-icon-edit'
                )
            );
        } else {
            $retval = COM_createLink(
                $icon_arr['edit'],
                LOCATOR_ADMIN_URL . '/index.php?edit=x&amp;id=' .$A['id']);
        }
        break;

    case 'delete':
        if ($_CONF_GEO['_is_uikit']) {
            $retval = COM_createLink('',
                LOCATOR_ADMIN_URL . '/index.php?deletemarker=x&amp;id=' . $A['id'],
                array(
                    'title' => $LANG_ADMIN['delete'],
                    'onclick'=>"return confirm('{$LANG_GEO['confirm_delitem']}');",
                    'class' => 'uk-icon uk-icon-trash loc-icon-danger'
                )
            );
        } else {
            $retval = COM_createLink(
                $icon_arr['delete'],
                LOCATOR_ADMIN_URL . '/index.php?deletemarker=x&amp;id=' . $A['id'],
                array('title' => $LANG_ADMIN['delete'],
                        'onclick'=>"return confirm('{$LANG_GEO['confirm_delitem']}');")
            );
        }
        break;

    case 'is_origin':
    case 'enabled':
        $checked = $fieldvalue == 1 ? 'checked="checked"' : '';
        $retval .= "<input type=\"checkbox\" id=\"{$fieldname}_{$A['id']}\"
                    name=\"{$fieldname}_{$A['id']}\" $checked 
                    onclick='LOCtoggleEnabled(this, \"{$A['id']}\", \"$fieldname\", \"{$_CONF['site_url']}\");'>";
        break;

    case 'title':
        $retval = COM_createLink(stripslashes($fieldvalue),
                $_CONF['site_url'] . '/' . 
                $_CONF_GEO['pi_name'] . '/index.php?detail=x&id=' .
                $A['id']);
        break;

    case 'address':
        $retval = stripslashes($fieldvalue);
        break;

    default:
        $retval = $fieldvalue;
        break;
    }

    return $retval;

}


/**
 * Builds an admin list of locations.
 *
 * @return  string HTML for the location list
 */
function GEO_adminList()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACCESS, $_CONF_GEO, $LANG_GEO;

    USES_lib_admin();

    $header_arr = array(      # display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center'),
        array('text' => 'ID', 'field' => 'id', 'sort' => true),
        array('text' => $LANG_GEO['title'], 'field' => 'title', 'sort' => true),
        array('text' => $LANG_GEO['address'], 'field' => 'address', 
                'sort' => true),
        array('text' => $LANG_GEO['origin'], 'field' => 'is_origin', 
                'sort' => true, 'align' => 'center'),
        array('text' => $LANG_GEO['enabled'], 'field' => 'enabled', 
                'sort' => true, 'align' => 'center'),
        array('text' => $LANG_GEO['latitude'], 'field' => 'lat', 
                'sort' => true),
        array('text' => $LANG_GEO['longitude'], 'field' => 'lng', 
                'sort' => true),
        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 
                'sort' => false, 'align' => 'center'),
    );


    $defsort_arr = array('field' => 'title', 'direction' => 'asc');

    $text_arr = array(
        'has_extras' => true,
        'form_url' => LOCATOR_ADMIN_URL . '/index.php',
    );

    $query_arr = array('table' => 'locator_markers',
        'sql' => "SELECT * FROM {$_TABLES['locator_markers']} ",
        'query_fields' => array('title', 'address'),
        'default_filter' => 'WHERE 1=1'
        //'default_filter' => COM_getPermSql ()
    );

    $options_arr = array(
        'chkdelete' => true,
        'chkfield'  => 'id',
    );
    $form_arr = array();
    return ADMIN_list('locator', 'plugin_getListField_marker', $header_arr,
                    $text_arr, $query_arr, $defsort_arr, '', '', 
                    $options_arr, $form_arr);
}


/**
 * Returns a formatted field when managing user locations.
 *
 * @param   string  $fieldname  Name of field
 * @param   string  $fieldvalue Value of field
 * @param   array   $A          Array of all values
 * @param   array   $icon_arr   Array of icons
 * @return  string              String to display for the selected field
 */
function GEO_getListField_userloc($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_CONF_GEO, $LANG24, $LANG_GEO, $LANG_ADMIN;

    switch($fieldname) {
    case 'edit':
        $retval = COM_createLink(
            $icon_arr['edit'],
            LOCATOR_ADMIN_URL . "/index.php?mode=edituserloc&amp;id={$A['id']}"
            );
        break;

    case 'delete':
        $retval = COM_createLink(
            $icon_arr['delete'],
            LOCATOR_ADMIN_URL . "/index.php?mode=deleteuserloc&amp;id={$A['id']}",
                array('title' => $LANG_ADMIN['delete'],
                    'onclick'=>"return confirm('{$LANG_GEO['confirm_delitem']}');")
            );
        break;

    default:
        $retval = $fieldvalue;
        break;
    }

    return $retval;

}


/**
 * Creates an admin list to administer the user location cache. 
 * The cache is built from the user locations given in glFusion account 
 * settings.
 *
 * @return  string  HTML for the admin list
 */
function GEO_adminUserloc()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACCESS, $_CONF_GEO, $LANG_GEO;

    $header_arr = array(
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 
                'sort' => false),
        array('text' => $LANG_GEO['address'], 'field' => 'location', 
                'sort' => true),
        array('text' => $LANG_GEO['latitude'], 'field' => 'lat', 
                'sort' => true),
        array('text' => $LANG_GEO['longitude'], 'field' => 'lng', 
                'sort' => true),
        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 
                'sort' => false, 'align' => 'center'),
    );

    $defsort_arr = array('field' => 'location', 'direction' => 'asc');

    $text_arr = array(
        'has_extras' => true,
        'form_url' => LOCATOR_ADMIN_URL . '/index.php?userloc=x',
    );

    $query_arr = array('table' => 'locator_userloc',
        'sql' => "SELECT * FROM {$_TABLES['locator_userloc']} ",
        'query_fields' => array(),
        'default_filter' => ''
    );
    $form_arr = array();
    return ADMIN_list('locator', 'GEO_getListField_userloc', $header_arr,
                    $text_arr, $query_arr, $defsort_arr, '', '', '', $form_arr);
}


// If plugin is installed but not enabled, display an error and exit gracefully
if (!in_array('locator', $_PLUGINS)) {
    /** Include the 404 error page if needed */
    COM_404();
    exit;
}

// Only let admin users access this page
$isAdmin = SEC_hasRights($_CONF_GEO['pi_name'].'.admin');
if (!$isAdmin) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the dailyquote Admin page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    $display = COM_siteHeader();
    $display .= COM_startBlock($LANG_GEO['access_denied']);
    $display .= $LANG_GEO['access_denied_msg'];
    //$display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

$action = '';
$actionval = '';
$expected = array(
    'edit', 'moderate', 'approve', 'savemarker', 'submit', 'cancel', 
    'deletemarker', 'delitem', 'validate', 'userloc', 'mode', 
);
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        $actionval = $_POST[$provided];
        break;
    } elseif (isset($_GET[$provided])) {
    	$action = $provided;
        $actionval = $_GET[$provided];
        break;
    }
}
if ($action == 'mode') {
    $action = $actionval;
}
$id = isset($_REQUEST['id']) ? COM_sanitizeID($_REQUEST['id'], false) : '';
$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';

if (isset($_REQUEST['view'])) {
    $view = COM_applyFilter($_REQUEST['view']);
} else {
    $view = $action;
}

$content = '';      // initialize variable for page content
$A = array();       // initialize array for form vars

switch ($action) {
case 'toggleorigin':
    // Toggle the is_origin flag between 0 and 1
    $newval = (int)$_REQUEST['is_origin'];
    if ($newval == 1 || $newval == 0) {
        DB_query("UPDATE {$_TABLES['locator_markers']}
            SET is_origin=$newval
            WHERE id=$id");
    }
    $view = '';
    break;

case 'deletemarker':
//case $LANG_ADMIN['delete']:
    if ($id != '') {
        if ($action == 'moderate') {
            // Deleting from the submission queue
            Locator\Marker::Delete($id, 'locator_submission');
            echo COM_refresh($_CONF['site_url'] . '/admin/moderation.php');
        } else {
            // Deleting a production marker
            Locator\Marker::Delete($id);
        }
    }
    $view = '';
    break;

case 'delitem':
    if (is_array($_POST['delitem'])) {
        foreach($_POST['delitem'] as $key=>$id) {
            Locator\Marker::Delete($id);
        }
    }
    $view = '';
    break;

case 'approve':
    // Approve the submission.  Remove the oldid so it'll be treated as new
    $_POST['oldid'] = '';
    $M = new Locator\Marker();
    if ($M->Save($_POST) == '') {
        Locator\Marker::Delete($_POST['id'], 'locator_submission');
    } else {
        $msg = 7;
    }
    $view = '';
    break;

case 'savemarker':
    if (isset($_POST['oldid']) && !empty($_POST['oldid'])) {
        // Updateing an existing marker
        $M = new Locator\Marker($_POST['oldid']);
    } else {
        $M = new Locator\Marker();
        /*GEO_insertSubmission($_POST, $action);
        if ($action == 'moderate') {
            // return to moderation screen
            echo COM_refresh($_CONF['site_url'] . '/admin/moderation.php');
        }*/
    }
    $msg = $M->Save($_POST);
    if (!empty($msg)) {
        // hack, need to move this part into the 'view' switch section.
        $M->SetVars($_POST);
        $content .= $M->Edit();
    }
    break;

case 'deleteuserloc':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if ($id > 0) {
        DB_delete($_TABLES['locator_userloc'], 'id', $id);
    }
    $view = 'userloc';
    break;

case 'saveuserloc':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    $location = DB_escapeString($_POST['location']);
    $lat = (double)$_POST['lat'];
    $lng = (double)$_POST['lng'];
    if ($id > 0) {
        $sql = "UPDATE {$_TABLES['locator_userloc']} SET
                location='$location',
                lat=$lat,
                lng=$lng
            WHERE id=$id";
    } else {
        $sql = "INSERT INTO {$_TABLES['locator_userloc']}
                (location, lat, lng)
            VALUES
                ($location, $lat, $lng)";
    }
    @DB_query($sql, 1);
    $view = 'userloc';
    break;

default:
    $view = $action;
    break;
}

switch($view) {
case 'userloc':
    $content .= GEO_adminUserloc();
    break;

case 'edit':
case 'editloc':
    $M = new Locator\Marker($id);
    $content .= $M->Edit();
    break;

case 'editsubmission':
case 'moderate':
    if ($id != '') {
       $result = DB_query("SELECT * from {$_TABLES['locator_submission']}
                WHERE id='$id'");
        if ($result && DB_numRows($result) == 1) {
            $A = DB_fetchArray($result);
            $M = new Locator\Marker();
            $M->SetVars($A, true);
            $content .= $M->Edit('', $action);
         }
    }
    break;

case 'edituserloc':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    require_once $_CONF['path'] . '/plugins/locator/edituserloc.php';
    $A = DB_fetchArray(DB_query("SELECT * FROM {$_TABLES['locator_userloc']}
            WHERE id=$id"));
    $content .= GEO_userlocForm($A);
    $view = 'none';
    break;

case 'none':    // display nothing, it was handled earlier
    break;

default:
    $content .= GEO_adminList();
    break;
}

$display = COM_siteHeader();
if (!empty($msg)) {
    $display .= COM_showMessage($msg, $_CONF_GEO['pi_name']);
}
$display .= GEO_adminMenu($view);
$display .= $content;
$display .= COM_siteFooter();
echo $display;

?>
