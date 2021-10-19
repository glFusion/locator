<?php
/**
 * English language file for the Locator plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2020 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     v1.2.1
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */

/**
 * Main language string array.
 * @global array $LANG_GEO
 */
$LANG_GEO = array(
'pi_title'          => 'Locator Service',
'item_id'           => 'Item ID',
'access_denied'     => 'Access Denied',
'access_denied_msg' => 'Only Root Users have Access to this Page.  Your user name and IP have been recorded.',
'address'           => 'Address',
'city'              => 'City',
'state'             => 'State',
'postal'            => 'Postal Code',
'country'           => 'Country',
'admin_menu'        => 'Geo Locator',
'anonview'          => 'Allow anonymous users to use the locator',
'coord'             => 'Coordinates',
'coord_instr'       => 'Enter coordinates as decimal numbers.  South Latitude and West Longitude should be entered as negative values. ',
'coord_instr2'      => 'If either coordinate is left empty, Google Maps will be used to fill them both from the given address',
'contrib_origin'    => 'Submit a Location',
'description'       => 'Description',
'disclaimer'        => 'Distances shown are approximate, and represent the actual linear distance from the origin.  Driving distances will be longer.',
'distance'          => 'Distance',
'editmarker'        => 'Edit a Location Marker',
'group'             => 'Group',
'have_new_submission'   => 'You have a new location submission in the queue.',
'new_submission'    => 'New Location Submission',
'home'              => 'Home',
'is_origin'         => 'Is Origin',
'enabled'           => 'Enabled',
'keywords'          => 'Keywords',
'latlng'            => 'Lat/Lng',
'latitude'          => 'Latitude',
'longitude'         => 'Longitude',
'login_required'    => 'You must be logged in to use this function',
'menulabel'         => 'Geo Locator',
'miles'             => 'Miles',
'manage_userlocs'   => 'Manage User Locations',
'my_origins'        => 'My Origins',
'name'              => 'Name',
'no_locs_found'     => 'No results found.',
'origin'            => 'Origin',
'origin_add'        => 'Set as a default origin',
'origin_remove'     => 'Remove from the default origins',
'origin_true'       => 'This location is set as an origin',
'owner'             => 'Owner',
'perms'             => 'Permissions',
'plugin_name'       => 'Geo Locator',
'radius'            => 'Radius',
'reset'             => 'Reset',
'speedlimit_exceeded' => 'You must allow ' . $_CONF['speedlimit'] . ' seconds between lookups.',
'title'             => 'Title',
'url'               => 'Web Site',
'version'           => 'Version',
'manage_locations'  => 'Manage Locations',
'desc_admin_locs'   => 'Manage global locations',
'desc_admin_userlocs' => 'Manage user locations.  These are updated automatically from the "location" field in the user profile. They may be edited to change the coordinates, and they may be deleted.  Simply changing the name will cause a new record to be created with the old name.',
'editor_mode'       => 'Editor Mode',
'db_save_error'     => 'Your entry could not be saved as entered.  Most likely there is a duplicate item ID value',
'confirm_delitem'   => 'Are you sure you want to delete this item?',
'get_directions'    => 'Get Directions',
'start_addr'        => 'Starting Address',
'or_address'        => 'or address',
'select_origin'     => 'Select Origin',
'menu_hlp'  => array(
    'userloc' => 'Edit or delete user locations.  These are origins that were entered as addresses in the query form, or are derived from the user profile "location" field.',
    'edit' => '',
    ),
'back'              => 'Back',
'location'          => 'Location',
'loc_updated'       => 'Location has been updated',
'notify_subject'    => 'Location Submission from ' . $_CONF['site_name'],
'from_origin'       => 'From Origin',
'at_dscp_locator'   => 'Show a map. ]locator:map (id:loc_id | addr:address) width:px height:px]',
);

$PLG_locator_MESSAGE1 = 'Your location has been queued for administrator approval.';
$PLG_locator_MESSAGE2 = 'Your location has been saved.';
$PLG_locator_MESSAGE3 = 'Error retrieving current version number';
$PLG_locator_MESSAGE4 = 'Error performing the plugin upgrade';
$PLG_locator_MESSAGE5 = 'Error upgrading the plugin version number';
$PLG_locator_MESSAGE6 = 'Plugin is already up to date';
$PLG_locator_MESSAGE7 = 'There was an error saving the marker.  See the error.log';
$PLG_locator_MESSAGE8 = 'Error: Duplicate marker ID';

$PLG_locator_MESSAGE99 = 'A database error occurred.  Check your error log for details.';


/**
 * Localization of the Admin Configuration UI.
 * @global array $LANG_configsections['locator']
 */
$LANG_configsections['locator'] = array(
    'label' => 'Geo Locator',
    'title' => 'Geo Locator Configuration'
);

/**
 * Configuration system prompt strings.
 * @global array $LANG_confignames['locator']
 */
$LANG_confignames['locator'] = array(
    'default_radius'    => 'Default Search Radius:',
    'distance_unit'     => 'Distance Units:',
    'autofill_coord'    => 'Automatically fill undefined coordinates:',
    'submission'        => 'Use submission queue:',
    'submit'            => 'Allow anonymous or member submissions?',
    'displayblocks'     => 'Display glFusion Blocks',
    'purge_userlocs'    => 'Days to cache user-entered search locations:',
    'profile_showmap'   => 'Show maps in user profiles?',
    'usermenu_option'   => 'Show on user menu?',
    'use_weather'       => 'Integrate with Weather plugin?',
    'use_directions'    => 'Offer to get directions on detail page?',
    'api_only'          => 'Operate in API-Only mode?',

    'geocode_profile'   => 'Geocode profile locations?',
    'show_map'          => 'Show Map?',
    'google_api_key'    => 'Google Maps API Key',
    'google_js_key'     => 'Google Maps JS Key',
    'here_js_key'       => 'HERE Javascript Key',
    'here_rest_key'     => 'HERE REST API Key',
    'mapbox_token'      => 'MapBox Access Token',

    'defgrp'            => 'Default Group:',
    'default_permissions' => 'Default Permissions:',
    'mapper'            => 'Map Provider',
    'geocoder'          => 'Geocoding Provider',
    'mapquest_key'      => 'Mapquest Client Key',
    'geocodio_api_key'  => 'Geocodio API Key',
    'osm_use_tileserver' => 'Use internal tileserver for OSM',
    'def_country'       => 'Default Country (2-letter code)',
);

/**
 * Configuration system subgroup strings.
 * @global array $LANG_configsubgroups['locator']
 */
$LANG_configsubgroups['locator'] = array(
    'sg_main' => 'Main Settings'
);

/**
 * Configuration system fieldset names.
 * @global array $LANG_fs['locator']
 */
$LANG_fs['locator'] = array(
    'fs_main' => 'General Settings',
    'fs_mappers' => 'Map Providers',
    'fs_permissions' => 'Default Permissions',
 );

/**
 * Configuration system selection strings.
 * Note: entries 0, 1, and 12 are the same as in 
 * $LANG_configselects['Core'].
 *
 * @global array $LANG_configselects['locator']
 */
$LANG_configselects['locator'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE),
    3 => array('Yes' => 1, 'No' => 0),
    4 => array('On' => 1, 'Off' => 0),
    5 => array('Top of Page' => 1, 'Below Featured Article' => 2, 'Bottom of Page' => 3),
    10 => array('5' => 5, '10' => 10, '25' => 25, '50' => 50),
    11 => array('Miles' => 'miles', 'Kilometres' => 'km'),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
    13 => array('None' => 0, 'Left' => 1, 'Right' => 2, 'Both' => 3),
    15 => array('Admins Only' => 0, 'Logged-In Users' => 1, 'Logged-In+Anon Users' => 2),
);

?>
