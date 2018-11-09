<?php
/**
 * Plugin-specific functions for the Locator plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2017 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.1.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

/**
 * Gets and displays all locations within $radius $units of $id.
 *
 * @param   string  $id     ID of location to use as origin
 * @param   integer $radius Radius, in $units
 * @param   string  $units  Unit of measure, 'km' or 'miles'
 * @param   string  $keywords   Search string
 * @param   string  $address    Optional street address to use as origin
 * @return  string  Content for web page
 */
function GEO_showLocations($id, $radius=0, $units='', $keywords='', $address='')
{
    global $_TABLES, $_CONF_GEO, $_CONF, $_USER, $LANG_GEO;

    $content = '';
    $locations = '';
    $errmsg = '';

    if ($units == '')
        $units = $_CONF_GEO['distance_unit'];

    if ($radius == 0)
        $radius = $_CONF_GEO['default_radius'];

    $url_opts = '&origin=' . urlencode($id).
        '&radius=' . (int)$radius.
        '&units=' . urlencode($units).
        '&keywords=' . urlencode($keywords).
        '&address=' . urlencode($address);

    $T = new Template($_CONF['path'] . 'plugins/locator/templates');
    $T->set_file('page', 'loclist.thtml');
    $T->set_var(array(
        'action_url'    => $_SERVER['PHP_SELF'],
        'origin_select' => GEO_originSelect($id),
        'radius_val'    => $radius == 0 ?
                            $_CONF_GEO['default_radius'] : $radius,
        'keywords'      => $keywords,
        'units'         => $units,
        'address'       => $address,
    ) );

    if ($units == 'km' ) {
        $T->set_var('km_selected', 'selected="selected"');
        $T->set_var('miles_selected', '');
    } else {
        $T->set_var('km_selected', '');
        $T->set_var('miles_selected', 'selected="selected"');
    }

    if ($_CONF_GEO['autofill_coord'] == 1) {
        $T->set_var('do_lookup', 'true');
    }

    if ($address != '' && $_CONF_GEO['autofill_coord']) {
        // user-supplied address.  Check the speedlimit to avoid
        // hammering Google.
        $id = '';        // clear the id since the address is used
        COM_clearSpeedlimit($_CONF['speedlimit'],
                    $_CONF_GEO['pi_name'].'lookup');
        $last = COM_checkSpeedlimit($_CONF_GEO['pi_name'].'lookup');
        if ($last > 0) {
            $errmsg = $LANG_GEO['speedlimit_exceeded'];
        } elseif (GEO_getCoords($address, $lat, $lng) == 0) {
            $locations = getLocsByCoord($lat, $lng, $radius, $units, $keywords);
            COM_updateSpeedlimit($_CONF_GEO['pi_name'].'lookup');
        }
    } elseif ($id != 'user') {
        // Get all locations within $radius
        $locations = getLocsByID($id, $radius, $units, $keywords);
    } elseif (!COM_isAnonUser()) {
        // use user profile location
        $user_location = DB_getItem($_TABLES['userinfo'], 'location',
                'uid='. $_USER['uid']);
        $userloc = new Locator\UserLoc($user_location);
        if ($userloc->lat != 0 && $userloc->lng != 0) {
            $locations = getLocsByCoord($userloc->lat, $userloc->lng, $radius,
                    $units, $keywords);
        }
    }

    if (is_array($locations)) {
        for ($i = 0; $i < count($locations); $i++) {
            // The origin is in this array, so skip it
            if ($locations[$i]['id'] == $id) continue;

            $T->set_block('page', 'LocRow', 'LRow');
            if ($locations[$i]['is_origin'] == 0) {
                $T->set_var('loc_url', LOCATOR_URL .
                    '/index.php?ddorigin=x&origin=' .
                    $locations[$i]['id'] . '&id=' . $locations[$i]['id'] .
                    '&radius=' . $radius);
            } else {
                $T->set_var('loc_url', '');
            }
            $T->set_var(array(
                'loc_id'    => $locations[$i]['id'],
                'loc_name'  => $locations[$i]['title'],
                'loc_address' => $locations[$i]['address'],
                'loc_distance' => sprintf("%4.2f", $locations[$i]['distance']),
                'loc_info_url' => LOCATOR_URL .
                                '/index.php?detail=x&id=' .
                                $locations[$i]['id'] . $url_opts,
                'loc_lat'   => $locations[$i]['lat'],
                'loc_lng'   => $locations[$i]['lng'],
                'url_opts'  => $url_opts,
                'adblock'   => PLG_displayAdBlock('locator_list', $i+1),
            ) );
            if ($locations[$i]['is_origin'] == 1 ||
                    $locations[$i]['userOrigin'] != NULL) {
                $T->set_var(array(
                    'ck_origin' => 'checked="checked"',
                    'img_origin' => 'on.png',
                    'img_origin_title' =>
                            'This location is already an available origin',
                ) );
            } else {
                $T->set_var(array(
                    'ck_origin' => 'checked=""',
                    'img_origin' => 'off.png',
                    'img_origin_title' =>
                        'Click to add this location as an available origin',
                ) );
            }
            $T->parse('LRow', 'LocRow', true);
        }
    } else {
        if ($errmsg == '')
            $errmsg = $LANG_GEO['no_locs_found'];
        $T->set_var('no_display', $errmsg);
    }

    $T->parse('output','page');
    $content .= $T->finish($T->get_var('output'));

    return $content;
}


/**
 * Gets all the locations with $radius $units of location $id.
 *
 * @param   integer $id     ID number of origin
 * @param   integer $radius Radius, in $units
 * @param   string  $units  Unit of measure, 'km' or 'miles'
 * @param   string  $keywords   Search keywords to limit result set
 * @return  array   Array of location records
 */
function getLocsByID($id, $radius=0, $units='', $keywords='')
{
    global $_TABLES, $_CONF_GEO, $_USER;

    $values = array();

    if ($id == '' || $radius < 1)
        return $values;

    // Get the origin coordinates, or call Google to get
    // the coordinates of the supplied address.
    // Return an error if either is empty.
    $sql = "SELECT lat, lng
                FROM {$_TABLES['locator_markers']}
                WHERE id='$id'";
    //echo $sql;
    list($lat,$lng) = DB_fetchArray(DB_query($sql));

    if (empty($lat) || empty($lng)) {
        return "Error retrieving source<br />\n";
    }

    $values = getLocsByCoord($lat, $lng, $radius, $units, $keywords);
    return $values;
}


/**
 * Actually performs the search for location within $radius $units of $lat/$lng.
 *
 * @param  float   $lat    Origin Latitude
 * @param  float   $lng    Origin Longitude
 * @param  integer $radius Radius from origin to include
 * @param  string  $units  Unit of measure for radius.  'km' or 'miles'
 * @param  string  $keywords   Search keywords to limit results
 * @return array   Array of location records matching criteria
 */
function getLocsByCoord($lat, $lng, $radius, $units='', $keywords='')
{
    global $_TABLES, $_CONF_GEO, $_USER;

    if ($units == '')
        $units = $_CONF_GEO['distance_unit'];
    if ($units == 'km') {
        $factor = 6371;
    } else {
        $factor = 3959;
    }

    $radius = (int)$radius;
    $values = array();
    $lat = (float)$lat;
    $lng = (float)$lng;
    if ($lat == 0 || $lng == 0) {
        // If invalid coordinates, return empty array
        return $values;
    }

    // Replace commas in lat & lng with decimal points
    $lat = GEO_coord2str($lat, true);
    $lng = GEO_coord2str($lng, true);

     // Find all the locations, excluding the origin, within the radius
    $sql = "SELECT
            m.*,
            ( $factor * acos(
                cos( radians($lat) ) *
                cos( radians( lat ) ) *
                cos( radians( lng ) - radians($lng) ) +
                sin( radians($lat) ) *
                sin( radians( lat ) )
            ) ) AS distance,";
    if (!COM_isAnonUser()) {
        $sql .= " (SELECT uid FROM {$_TABLES['locator_userXorigin']} u
                where u.uid={$_USER['uid']} and u.mid=m.id) as userOrigin ";
    } else {
        $sql .= " NULL as userOrigin ";
    }
    $sql .= " FROM {$_TABLES['locator_markers']} m
            WHERE enabled = 1 ";
    if (isset($_GET['origin'])) {
        $sql .= " AND id <> '" . DB_escapeString($_GET['origin']) . "' ";
    }
    if ($keywords != '') {
        $kw_esc = explode(' ', DB_escapeString(trim($keywords)));
        foreach ($kw_esc as $kw) {
            $sql .= " AND (keywords LIKE '%$kw%'
                OR title LIKE '%$kw%'
                OR description LIKE '%$kw%'
                OR address LIKE '%$kw%'
                OR url LIKE '%$kw%')";
        }
    }
    $sql .= COM_getPermSQL('AND', 0, 2, 'm');
    $sql .= " HAVING distance < $radius
        ORDER BY distance
        LIMIT 0, 200";

    $result = DB_query($sql);
    if (!$result)
        return "Error reading from database";

    while ($record = DB_fetchArray($result)) {
        $values[] = $record;
    }
    return $values;
}


/**
 * Creates a dropdown list of origins.
 * Includes user-selected origins, if any.
 *
 * @param   string  $id     Optional ID of origin to be selected
 * @return  string  HTML of selection list
 */
function GEO_originSelect($id)
{
    global $_USER, $_TABLES;

    $retval = '';

    // Find the user-specific origins, if any.
    if (!COM_isAnonUser()) {
        $sql = "SELECT DISTINCT m.id,m.title
            FROM {$_TABLES['locator_markers']} m
            LEFT JOIN {$_TABLES['locator_userXorigin']} u
            ON u.mid=m.id
            WHERE u.uid = {$_USER['uid']}
            OR m.is_origin=1";
        $result = DB_query($sql);
        while ($row = DB_fetchArray($result, false)) {
            $selected = $row['id'] == $id ? 'selected ' : '';
            $retval .= "<option value=\"{$row['id']}\" $selected>{$row['title']}</option>\n";
        }

        // Add the user's own location, if any, and select if it's the chosen one.
        $selected = $id == 'user' ? 'selected' : '';
        $userloc = DB_getItem($_TABLES['userinfo'],
                'location', "uid=".$_USER['uid']);
        if ($userloc != '')
            $retval .= "<option value=\"user\" $selected>{$userloc}</option>\n";

    } else {
        // Get the systemwide origins
        $retval = COM_optionList($_TABLES['locator_markers'],
            'id,title', $id, 1, "is_origin=1 or id='$id'");
    }

    return $retval;
}


/**
 * Adds a system marker to the user origin table for the current user.
 *
 * @param   string $id   Marker ID to add as a user's origin
 */
function GEO_addUserOrigin($id='')
{
    global $_USER, $_TABLES;

    if (COM_isAnonUser()) return;
    // If $id is empty, or this user/origin combo is in the table, just return.
    if ($id == '') return;
    if (DB_count($_TABLES['locator_userXorigin'],
            array('uid','mid'),
            array($_USER['uid'], $id)) > 0)
        return;

    $sql = "INSERT INTO {$_TABLES['locator_userXorigin']}
            (uid, mid)
        VALUES
            ({$_USER['uid']}, '$id')";
    //echo $sql;die;
    DB_query($sql);
}


/**
 * Removes a system marker from the user origin table for the current user.
 *
 * @param   string $id   Marker ID to add as a user's origin
 */
function GEO_delUserOrigin($id='')
{
    global $_USER, $_TABLES;

    if (COM_isAnonUser()) return;
    if ($id == '') return;

    DB_delete($_TABLES['locator_userXorigin'],
        array('uid', 'mid'),
        array($_USER['uid'], $id));
}


/**
 * Creates an administrator list to allow users to add origins to
 * their preferences.
 *
 * @return  string HTML of origin list
 */
function GEO_showOrigins()
{
    global $_CONF, $_TABLES, $_CONF_GEO, $LANG_GEO, $_USER;

    if (COM_isAnonUser()) return '';

    USES_lib_admin();

    $retval = '';

    $header_arr = array(      # display 'text' and use table field 'field'
//        array('text' => $LANG_GEO['origin'].'?', 'field' => 'is_origin',
//                'sort' => true, 'align' => 'center'),
        array('text' => 'ID', 'field' => 'id', 'sort' => true),
        array('text' => $LANG_GEO['title'], 'field' => 'title', 'sort' => true),
        array('text' => $LANG_GEO['address'], 'field' => 'address', 'sort' => true),
        array('text' => $LANG_GEO['latitude'], 'field' => 'lat',
                'sort' => true, 'align' => 'right'),
        array('text' => $LANG_GEO['longitude'], 'field' => 'lng',
                'sort' => true, 'align' => 'right'),
    );

    $defsort_arr = array('field' => 'location', 'direction' => 'asc');

    $retval .= COM_startBlock($LANG_GEO['admin_menu'], '',
                COM_getBlockTemplate('_admin_block', 'header'));

    $text_arr = array(
        'has_extras' => true,
        'form_url' => LOCATOR_URL . '/index.php?page=myorigins',
    );
    $form_arr = array();

    $query_arr = array('table' => 'locator_userloc',
        'sql' => "SELECT * FROM {$_TABLES['locator_userloc']}",
        'query_fields' => array('location'),
        'default_filter' => "WHERE uid={$_USER['uid']}",
    );

    $retval .= ADMIN_list('locator', 'GEO_getAdminListField', $header_arr,
                    $text_arr, $query_arr, $defsort_arr, '', '', '', $form_arr);
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    return $retval;
}


/**
 * Returns a formatted field.
 *
 * @param   string  $fieldname  Name of field
 * @param   string  $fieldvalue Value of field
 * @param   array   $A          Array of all values
 * @param   array   $icon_arr   Array of icons
 * @return  string  String to display for the selected field
 */
function GEO_getAdminListField($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_CONF_GEO, $LANG24, $LANG_GEO;

    $retval = '';

    switch($fieldname) {
    case 'is_origin':
        $base_url = '';
        if ($A['is_origin'] == 1) {
            // admin-set as origin, no user selection allowed
            $icon = 'sys_origin.png';
            $mootip = $LANG_GEO['origin_true'];
            $base_url = '';
            $retval .= COM_createImage(LOCATOR_URL . '/images/' . $icon,
                        $mootip,
                        array('title'=>$mootip, 'class'=>'gl_mootip'));

        } else {
            $base_url = LOCATOR_URL . '/index.php';

             if ($A['userOrigin'] != NULL) {
                // user-selected origin, allow user to disable
                $checked = 'checked';
                $parms = '&is_origin=0';
                $is_origin = 0;     // toggle off if clicked
                $mootip = $LANG_GEO['origin_remove'];
            } else {
                // user-selected origin, allow user to enable
                $checked = '';
                $parms = '&is_origin=1';
                $is_origin = 1;     // toggle on if clicked
                $mootip = $LANG_GEO['origin_add'];
            }
            $retval .= "<form action=\"{$base_url}\" method=\"POST\">\n
                <input type=\"hidden\" name=\"id\" value=\"{$A['id']}\">\n
                <input type=\"hidden\" name=\"is_origin\" value=\"$is_origin\">\n
                <input type=\"hidden\" name=\"mode\" value=\"toggleorigin\">\n
                <input type=\"checkbox\" name=\"cbox[{$A['id']}]\" $checked onclick=\"submit()\">
                </form>\n";
        }
        break;

        case 'title':
            $retval = COM_createLink($fieldvalue,
                    LOCATOR_URL . '/index.php?detail=x&id=' . $A['id']);
            break;

        case 'address':
            $retval = $fieldvalue;
            break;

        default:
            $retval = $fieldvalue;
            break;
    }

    return $retval;
}


/**
 * Get the coordinates for a given address from cache.
 * First looks in the cache table for an exact match to the address.
 * If found, returns the coordinates.  If not, looks up the address via
 * Google, adds the information to the cache, and returns the found
 * information.
 *
 * @param   string  $address    Address to look up
 * @param   float   &$lat       Latitude variable to set
 * @param   float   &$lng       Longitude variable to set
 * @return  integer             Status
 */
function GEO_getCoordsUserAddress($address, &$lat, &$lng)
{
    $rec = new Locator\UserOrigin($address);
    $lat = $rec->lat;
    $lng = $rec->lng;
    if ($lat != 0 && $lng != 0)
        return 200;
    else
        return 0;
}


/**
 * Show the site header, with or without left blocks according to config.
 *
 * @since   version 1.0.1
 * @see     COM_siteHeader()
 * @param   string  $subject    Text for page title (ad title, etc)
 * @param   string  $meta       Other meta info
 * @return  string              HTML for site header
 */
function GEO_siteHeader($subject='', $meta='')
{
    global $_CONF_GEO, $LANG_GEO;

    $retval = '';

    $title = $LANG_GEO['pi_title'];
    if ($subject != '')
        $title = $subject . ' : ' . $title;

    switch($_CONF_GEO['displayblocks']) {
    case 2:     // right only
    case 0:     // none
        $retval .= COM_siteHeader('none', $title, $meta);
        break;

    case 1:     // left only
    case 3:     // both
    default :
        $retval .= COM_siteHeader('menu', $title, $meta);
        break;
    }

    return $retval;
}


/**
 * Show the site footer, with or without right blocks according to config.
 *
 * @since  version 1.0.1
 * @see    COM_siteFooter()
 * @return string              HTML for site header
 */
function GEO_siteFooter()
{
    global $_CONF_GEO;

    $retval = '';

    switch($_CONF_GEO['displayblocks']) {
    case 2 : // right only
    case 3 : // left and right
        $retval .= COM_siteFooter(true);
        break;

    case 0: // none
    case 1: // left only
    default :
        $retval .= COM_siteFooter();
        break;
    }
    return $retval;
}

?>
