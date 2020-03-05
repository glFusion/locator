<?php
/**
 * Administrator interface for the Locator plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2020 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.2.1
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */

/** Include required glFusion common functions */
require_once '../../../lib-common.php';

// If plugin is installed but not enabled, display an error and exit gracefully
// Only let admin users access this page
if (
    !in_array('locator', $_PLUGINS) ||
    !SEC_hasRights($_CONF_GEO['pi_name'].'.admin')
) {
    COM_404();
    exit;
}

$action = '';
$actionval = '';
$expected = array(
    'edit', 'edituserloc', 'moderate', 'approve', 'savemarker',
    'submit', 'cancel', 
    'deletemarker', 'deleteuserloc', 'delitem', 'validate', 'userloc', 'mode', 
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
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $Loc = new Locator\UserLoc($id);
    $Loc->setVars($_POST);
    if ($Loc->getLat() == 0 || $Loc->getLng() == 0) {
        $Loc->getCoords();
    }
    $Loc->saveToDB();
    $view = 'userloc';
    break;

default:
    $view = $action;
    break;
}

switch($view) {
case 'userloc':
    $content .= Locator\UserLoc::adminList();
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
    $content .= Locator\UserLoc::getByID($id)->Edit();
    $view = 'none';
    break;

case 'none':    // display nothing, it was handled earlier
    break;

default:
    $view = 'locations';
    $content .= Locator\Marker::adminList();
    break;
}

$display = COM_siteHeader();
if (!empty($msg)) {
    $display .= COM_showMessage($msg, $_CONF_GEO['pi_name']);
}
$display .= Locator\Menu::Admin($view);
$display .= $content;
$display .= COM_siteFooter();
echo $display;

?>
