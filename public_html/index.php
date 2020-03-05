<?php
/**
 *   Public entry point for the Locator plugin.
 *
 *   @author     Lee Garner <lee@leegarner.com>
 *   @copyright  Copyright (c) 2009-2020 Lee Garner <lee@leegarner.com>
 *   @package    locator
 *   @version    v1.2.1
 *   @license    http://opensource.org/licenses/gpl-2.0.php 
 *               GNU Public License v2 or later
 *   @filesource
 */

/** Include required common glFusion functions */
require_once '../lib-common.php';

// If plugin is installed but not enabled, display an error and exit
// Also exit if the plugin is enabled for API use only and not guest-facing
if (!in_array('locator', $_PLUGINS) ||
    (isset($_GEO_CONF['api_only']) && $_GEO_CONF['api_only'] == 1)
) {
    COM_404();
    exit;
}

// If login is required, but user is anonymous, show the login form
if ($_CONF['loginrequired'] == 1 && COM_isAnonUser()) {
    SEC_loginRequiredForm();
    exit;
}

// Retrieve and sanitize arguments and form vars
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$display = '';
$action = '';
$actionval = '';
$expected = array(
    'savemarker', 'detail', 'myorigins', 'submit',
    'mode', 
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
$radius = isset($_REQUEST['radius']) ? (int)$_REQUEST['radius'] : 0;
$keywords = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : '';
if (isset($_REQUEST['units']) && 
    in_array($_REQUEST['units'], array('km', 'miles'))
) {
    $units = $_REQUEST['units'];
} else {
    $units = $_CONF_GEO['distance_unit'];
}
$address = isset($_REQUEST['address']) ? trim($_REQUEST['address']) : '';
$origin = isset($_REQUEST['origin']) ? COM_sanitizeID($_REQUEST['origin']) : '';
$id = isset($_REQUEST['id']) ? COM_sanitizeID($_REQUEST['id']) : '';
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : $action;
$content = '';

switch($action) {
case 'savemarker':
    if (isset($_POST['oldid']) && !empty($_POST['oldid'])) {
        // Editing an existing marker
        $M = new Locator\Marker($_POST['oldid']);
    } else {
        $M = new Locator\Marker();
    }
    if (SEC_hasRights($_CONF_GEO['pi_name'].'.admin')) {
        $table = 'locator_markers';
        $success_msg = $PLG_locator_MESSAGE2;
    } else {
        $table = 'locator_submission';
        $success_msg = $PLG_locator_MESSAGE1;
    }
    $msg = $M->Save($_POST, $table);
    if (empty($msg)) {
        LGLIB_storeMessage($success_msg);
    } else {
        LGLIB_storeMessage($PLG_locator_MESSAGE99);
    }
    COM_refresh(LOCATOR_URL);
    break;

case 'toggleorigin':
    $newval = (int)$_REQUEST['is_origin'];
    if ($newval == 0) {
        Locator\UserOrigin::delete($id);
    } else {
        Locator\UserOrigin::add($id);
    }
    $view = 'myorigins';
    break;

default:
    $view = $action;
    break;
}

switch ($view) {
case 'myorigins':
    $content .= Locator\UserLoc::originList();
    break;

case 'detail':
    $M = new Locator\Marker($id);
    $back_url = LOCATOR_URL . '/index.php?loclist=x' .
            '&origin=' . urlencode($origin) .
            '&radius=' . (int)$radius .
            '&units=' . urlencode($units) .
            '&keywords=' . urlencode($keywords) .
            '&address=' . urlencode($address);
    $content .= $M->Detail($origin, $back_url);
    break;

case 'submit':
    // only valid users allowed
    if (!GEO_canSubmit()) {
        COM_404();
    }
    $M = new Locator\Marker();
    $content .= $M->Edit();
    break;

case 'loclist':
default:
    $content .= Locator\Marker::getLocations($origin, $radius, $units, $keywords, $address);
    break;
}

$display .= Locator\Menu::siteHeader();

if (!empty($msg)) {
    $display .= COM_showMessage((int)$msg, 'locator');
}

$T = new Template($_CONF['path'] . 'plugins/locator/templates');
$T->set_file('page', 'locator_header.thtml');
if (!COM_isAnonUser()) {
    $T->set_var('url_myorigins', 
            "<a href=\"{$_SERVER['PHP_SELF']}?myorigins=x\">" .
                $LANG_GEO['my_origins']. '</a>');
}
if (GEO_canSubmit()) {
    $T->set_var('url_contrib',  COM_createLink(
        $LANG_GEO['contrib_origin'],
        LOCATOR_URL . '/index.php?submit=x'
    ) );
}
$T->set_var('url_home', LOCATOR_URL . '/index.php');
$T->parse('output', 'page');
$display .= $T->finish($T->get_var('output'));
$display .= $content;

$display .= Locator\Menu::siteFooter();
echo $display;

?>
