<?php
/**
 * Marker class for the Locator plugin
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2020 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.2.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Locator;


/**
 * Class to handle the general location markers.
 * @package locator
 */
class Marker
{
    /** Radius for searching, in miles or KM.
     * @var float */
    private $radius = 0;

    /** Search keywords.
     * @var string */
    private $keywords = '';

    /** Latitude coordinate.
     * @var float */
    private $lat = 0;

    /** Longitude coordinate.
     * @var float */
    private $lng = 0;

    /** Marker title.
     * @var string */
    private $title = '';

    /** Description.
     * @var string */
    private $dscp = '';

    /** Street address.
     * @var string */
    private $address = '';

    /** City name.
     * @var string */
    private $city = '';

    /** State name.
     * @var string */
    private $state = '';

    /** Postal code.
     * @var string */
    private $postal = '';

    /** Enabled flag.
     * @var boolean */
    private $enabled = 1;

    /** Flag to indicate that this is a search origin.
     * @var boolean */
    private $is_origin = 0;

    /** Owner permission value.
     * @var integer */
    private $perm_owner = 3;

    /** Group permission value.
     * @var integer */
    private $perm_group = 2;

    /** Members permission value.
     * @var integer */
    private $perm_members = 2;

    /** Anomymous permission value.
     * @var integer */
    private $perm_anon = 2;

    /** Owner's user ID.
     * @var integer */
    private $owner_id = 1;

    /** Group ID.
     * @var intgeer */
    private $group_id = 2;

    /** View counter.
     * @var integer */
    private $views = 0;

    /** URL to the site's website.
     * @var string */
    private $url = '';

    /** Date added to the database (timestamp).
     * @var integer */
    private $add_date = 0;

    /** Indicate that the current user is a plugin administrator.
     * @var boolean */
    private $isAdmin = false;

    /** Flag to indicate a new marker vs. one read from the DB.
     * @var boolean */
    public $isNew = true;

    /** Distance from origin.
     * Set by caller, used for display only.
     * @var float */
    private $distFromOrigin = 0;


    /**
     * Constructor.
     *
     * @param   string  $id     Optional ID of a location to load
     */
    public function __construct($id = '')
    {
        global $_CONF_GEO, $_USER;

        $this->id = $id;
        $this->isNew = true;
        if ($this->id != '') {
            if ($this->Read()) {
                $this->isNew = false;
            };
        } else {
            $this->perm_owner = $_CONF_GEO['default_permissions'][0];
            $this->perm_group = $_CONF_GEO['default_permissions'][1];
            $this->perm_members = $_CONF_GEO['default_permissions'][2];
            $this->perm_anon = $_CONF_GEO['default_permissions'][3];
            $this->group_id = $_CONF_GEO['defgrp'];
            $this->owner_id = $_USER['uid'];
        }

        // Get the admin status from the plugin function since it's cached.
        $this->isAdmin = plugin_ismoderator_locator();
    }


    /**
     * Get the title string.
     *
     * @return  string      Marker title
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Set the longitude coordinate.
     *
     * @param   float   $lng    Longitude
     * @return  object  $this
     */
    public function setLng($lng)
    {
        $this->lng = (float)$lng;
        return $this;
    }


    /**
     * Get the longitude value.
     *
     * @return  float       Longitude
     */
    public function getLng()
    {
        return (float)$this->lng;
    }


    /**
     * Set the latitude coordinate.
     *
     * @param   float   $lat    Latitude
     * @return  object  $this
     */
    public function setLat($lat)
    {
        $this->lat = (float)$lat;
        return $this;
    }


    /**
     * Get the latitude value.
     *
     * @return  float       Latitude
     */
    public function getLat()
    {
        return (float)$this->lat;
    }


    /**
     * Set the distance from an origin.
     *
     * @param   float   $dist   Distance in Miles or KM
     * @return  object  $this
     */
    public function setDistance($dist)
    {
        $this->distFromOrigin = (float)$dist;
        return $this;
    }


    /**
     * Check if this is new or existing record.
     *
     * @return  boolean     1 if new, 0 if existing
     */
    public function isNew()
    {
        return $this->isNew ? 1 : 0;
    }


    /**
     * Read a marker from the database into variables.
     *
     * @param   string  $id     Optional ID of marker, or current is used
     * @return  boolean         True if found and read, False on error or not found
     */
    public function Read($id = '')
    {
        global $_TABLES;

        if ($id != '') $this->id = $id;
        $sql = "SELECT * FROM {$_TABLES['locator_markers']}
                WHERE id='{$this->id}'";
        $res = DB_query($sql, 1);
        if (!$res || DB_error()) return false;
        $A = DB_fetchArray($res, false);
        if (!$A) return false;
        $this->SetVars($A, true);
        return true;
    }


    /**
     * Set all variables from the database or form into the object
     *
     * @param   array   $A      Array of name=>value pairs
     * @param   boolean $fromDB TRUE if $A is from the DB, FALSE if a form
     */
    public function SetVars($A, $fromDB=false)
    {
        if (empty($A) || !is_array($A)) return false;

        $this->id = $A['id'];
        $this->lat = $A['lat'];
        $this->lng = $A['lng'];
        $this->keywords = $A['keywords'];
        $this->address = $A['address'];
        $this->city = $A['city'];
        $this->state = $A['state'];
        $this->postal = $A['postal'];
        $this->owner_id = $A['owner_id'];
        $this->group_id = $A['group_id'];
        $this->title = $A['title'];
        $this->description = $A['description'];
        $this->url = $A['url'];

        // Some values come in differently if from a form vs. the DB
        if ($fromDB) {
            $this->is_origin = $A['is_origin'];
            $this->views = $A['views'];
            $this->add_date = $A['add_date'];
            $this->enabled = $A['enabled'];

            $this->perm_owner = $A['perm_owner'];
            $this->perm_group = $A['perm_group'];
            $this->perm_members = $A['perm_members'];
            $this->perm_anon = $A['perm_anon'];
            $this->oldid = $A['id'];
        } else {            // from a form
            // Don't even set add_date or views- that's done during Save
            $this->is_origin = isset($A['is_origin']) ? 1 : 0;
            list($this->perm_owner, $this->perm_group,
                $this->perm_members, $this->perm_anon) =
                SEC_getPermissionValues($A['perm_owner'], $A['perm_group'],
                    $A['perm_members'], $A['perm_anon']);
            $this->enabled = isset($A['enabled']) ? 1 : 0;
            $this->oldid = isset($A['oldid']) ? $A['oldid'] : '';
        }

    }


    /**
     * Delete a single marker, and all category assignments.
     *
     * @param   string  $id     ID of marker to delete
     * @param   string  $table  Table identifier (prod or submission)
     */
    public function Delete($id, $table='locator_markers')
    {
        global $_TABLES;

        if ($id == '' && is_object($this)) {
            $id = $this->id;
        }
        if ($table != 'locator_markers') $table = 'locator_submission';
        if (!empty($id)) {
            // Delete the marker
            DB_delete($_TABLES[$table], 'id', DB_escapeString($id));
        }
    }


    /**
     * Updates an existing marker in either the live or submission table.
     *
     * @param   array   $A      Form data
     * @param   string  $table  Table to update
     */
    public function Save($A, $table='locator_markers')
    {
        global $_TABLES, $_USER, $_CONF_GEO, $_CONF, $LANG_GEO;

        // This is a system error of some kind.  Ignore
        if (!is_array($A) || empty($A)) {
            return 0;
        }

        if ($A['id'] == '') {       // id field was cleared, restore it.
            if ($A['oldid'] != '') {
                $A['id'] = $A['oldid'];
            } else {
                $A['id'] = COM_makeSid();
            }
        }
        $this->SetVars($A);

        if ($table != 'locator_submission') {
            $table = $_TABLES['locator_markers'];
        } else {
            $table = $_TABLES['locator_submission'];
        }

        // If either coordinate is missing, AND there is an address, AND
        // autofill_coord is configured as 'true', then get the coordinates
        // from the geocoder.
        $lat = $this->lat;      // convert to "real" variables
        $lng = $this->lng;      // so the pointer can be passed
        if (
            (empty($lat) || empty($lng)) &&
            $_CONF_GEO['autofill_coord'] == true
        ) {
            $address = $this->AddressToString();
            if ($address != '' && GEO_getCoords($address, $lat, $lng) == 0) {
                $this->lat = $lat;
                $this->lng = $lng;
            }
        }

       // Force floating-point format to use decimal in case locale is different.
        $lat = GEO_coord2str($this->lat, true);
        $lng = GEO_coord2str($this->lng, true);

        $sql1 = "title = '" . DB_escapeString($this->title) . "',
            address = '" . DB_escapeString($this->address) . "',
            city = '" . DB_escapeString($this->city) . "',
            state = '" . DB_escapeString($this->state) . "',
            postal = '" . DB_escapeString($this->postal) . "',
            description = '" . DB_escapeString($this->description) . "',
            lat = {$lat},
            lng = {$lng},
            keywords = '" . DB_escapeString($this->keywords) . "',
            url = '" . DB_escapeString($A['url']) . "',
            is_origin = '{$this->is_origin}',
            enabled = '{$this->enabled}',
            owner_id = '{$this->owner_id}',
            group_id = '{$this->group_id}',
            perm_owner = '{$this->perm_owner}',
            perm_group = '{$this->perm_group}',
            perm_members = '{$this->perm_members}',
            perm_anon = '{$this->perm_anon}' ";

        if ($this->oldid != '') {
            // Check for duplicates, since the user might have changed the
            // marker ID.  Not necessary if (newid == oldid)
            // Does this need to check the submission table?  Don't think so.
            if ($this->id != $this->oldid) {
                if (DB_count($table, 'id', $this->id)) {
                    return 8;
                }
            }
            $sql = "UPDATE $table SET
                    id = '{$this->id}',
                    $sql1
                    WHERE id = '{$this->oldid}'";
        } else {
            // Check for duplicate IDs since it's a common error that we'd
            // like to report accurately to the user. Check both the
            // production and submission tables, if needed.
            if ($table == $_TABLES['locator_submission'] &&
                DB_count($table, 'id', $this->id)) {
                return 8;
            }
            if (DB_count($_TABLES['locator_markers'], 'id', $this->id)) {
                return 8;
            }
            $sql = "INSERT INTO $table SET
                id = '{$this->id}',
                $sql1";
        }
        //echo $sql;die;
        DB_query($sql, 1);
        if (DB_error()) {
            COM_errorLog("SQL Error: $sql");
            return 99;
        } else {
            if ($table == $_TABLES['locator_submission'] &&
                isset($_CONF['notification']) &&
                in_array ('locator', $_CONF['notification'])) {
                $N = new \Template(LOCATOR_PI_PATH . '/templates/notify');
                $N->set_file('mail', 'submission.thtml');
                $N->set_var(array(
                    'title'     => $this->title,
                    'summary'   => $this->address,
                    'submitter' => COM_getDisplayName($this->owner_id),
                ) );
                $N->parse('output', 'mail');
                $mailbody = $N->finish($N->get_var('output'));
                $subject = $LANG_GEO['notify_subject'];
                $to = COM_formatEmailAddress('', $_CONF['site_mail']);
                COM_mail($to, $subject, $mailbody, '', true);
            }
            return 0;
        }
    }


    /**
     * Display the marker edit form
     *
     * @param   string  $id     Optional ID to load & edit, current if empty
     * @param   string  $mode   Optional mode indicator to set form action
     */
    public function Edit($id = '', $mode='submit')
    {
        global $_CONF_GEO, $_TABLES, $_CONF, $LANG24, $LANG_postmodes, $_SYSTEM,
                $LANG_GEO;

        $retval = '';
        if ($id != '') {
            $this->Read($id);
        } elseif ($this->id == '') {
            $this->id = COM_makeSid();
        }

        //displays the add quote form for single quotations
        $T = new \Template(LOCATOR_PI_PATH . '/templates');
        $T->set_file('page', 'markerform.thtml');

        // Set up the wysiwyg editor, if available
        switch (PLG_getEditorType()) {
        case 'ckeditor':
            $T->set_var('show_htmleditor', true);
            PLG_requestEditor('locator','locator_entry','ckeditor_locator.thtml');
            PLG_templateSetVars('locator_entry', $T);
            break;
        case 'tinymce' :
            $T->set_var('show_htmleditor',true);
            PLG_requestEditor('locator','locator_entry','tinymce_locator.thtml');
            PLG_templateSetVars('locator_entry', $T);
            break;
        default :
            // don't support others right now
            $T->set_var('show_htmleditor', false);
            break;
        }

        // Set up the save action
        switch ($mode) {
        case 'moderate':
            $saveaction = 'approve';
            break;
        default:
            $saveaction = 'savemarker';
            break;
        }

        $T->set_var(array(
            'id'            => $this->id,
            'oldid'         => $this->oldid,
            'title'         => $this->title,
            'description'   => $this->description,
            'address'       => $this->address,
            'city'          => $this->city,
            'state'         => $this->state,
            'postal'        => $this->postal,
            'lat'           => $this->lat,
            'lng'           => $this->lng,
            'keywords'      => $this->keywords,
            'url'           => $this->url,
            'origin_chk'    => $this->is_origin == 1 ? 'checked="checked"' : '',
            'enabled_chk'   => $this->enabled == 1 ? 'checked="checked"' : '',
            //'post_options'  => $post_options,
            'permissions_editor' => SEC_getPermissionsHTML(
                            $this->perm_owner, $this->perm_group,
                            $this->perm_members, $this->perm_anon),
            'pi_name'       => $_CONF_GEO['pi_name'],
            'action'        => $mode,
            'saveaction'     => $saveaction,
        ) );

        if ($_CONF_GEO['autofill_coord'] != '') {
            $T->set_var('goog_map_instr', $LANG_GEO['coord_instr2']);
        }

        if ($this->isAdmin) {
            $T->set_var(array(
                'action_url'    => LOCATOR_ADMIN_URL . '/index.php',
                'cancel_url'    => LOCATOR_ADMIN_URL . '/index.php',
                'ownerselect'   => COM_optionList($_TABLES['users'],
                            'uid,username', $this->owner_id, 1),
                'group_dropdown' => SEC_getGroupDropdown($this->group_id, 3),
                'show_del_btn' => $this->oldid != '' && $mode != 'submit' ?
                            'true' : '',
            ) );
        } else {
            $T->set_var(array(
                'action_url'    => LOCATOR_URL . '/index.php',
                'cancel_url'    => LOCATOR_URL . '/index.php',
                'owner_id'      => $this->owner_id,
                'group_id'      => $this->group_id,
                'ownername'     => COM_getDisplayName($this->owner_id),
                'groupname'     => DB_getItem($_TABLES['groups'], 'grp_name',
                            "grp_id={$this->group_id}"),
            ) );
        }

        $T->parse('output','page');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }


    /**
     * Increment the hit counter for a marker
     *
     * @param   string  $id     Marker ID
     */
    public static function Hit($id)
    {
        global $_TABLES;

        DB_Query("UPDATE {$_TABLES['locator_markers']}
            SET views = views + 1
            WHERE id = '" . COM_sanitizeId($id) . "'");
    }


    /**
     * Displays the location's information, along with a map.
     * May be expanded in the future to use $origin to create driving
     * directions.
     *
     * @param   string  $origin     Optional origin ID, used to create directions
     * @param   string  $back_url   Return URL (deprecated)
     * @return  string  HTML displaying location with map
     */
    public function Detail($origin='', $back_url='')
    {
        global $_CONF, $_CONF_GEO;

        if ($this->id == '')
            return 'Error : ID is empty';

        $retval = '';
        //$origin= COM_sanitizeID($origin);
        $srchval = isset($_GET['query']) ? trim($_GET['query']) : '';

        self::Hit($this->id);

        // Highlight search terms, if any
        if ($srchval != '') {
            $title = COM_highlightQuery($this->title, $srchval);
            $description = COM_highlightQuery($this->description, $srchval);
            $address = COM_highlightQuery($this->address, $srchval);
            // Don't do the url, the quotes get messed up.
        } else {
            $title = $this->title;
            $description = $this->description;
            $address = $this->AddressToString('<br />');
        }

        $T = new \Template(LOCATOR_PI_PATH . '/templates');
        $T->set_file('page', 'locinfo.thtml');
        $info_window = $this->title;
        foreach (array('address', 'city', 'state', 'postal') as $fld) {
            if ($this->$fld != '') {
                $info_window .= '<br />' . htmlspecialchars($this->$fld);
            }
        }
        $T->set_var(array(
            'is_admin'          => $this->isAdmin,
            'loc_id'            => $this->id,
            'action_url'        => $_SERVER['PHP_SELF'],
            'name'              => $this->title,
            'address'           => $this->AddressToHTML(),
            //'city'              => $this->city,
            //'state'             => $this->state,
            //'postal'            => $this->postal,
            'description'       => $this->description,
            'url'               => COM_createLink($this->url, $this->url,
                                    array('target' => '_new')),
            'lat'               => GEO_coord2str($this->lat),
            'lng'               => GEO_coord2str($this->lng),
            'back_url'          => $back_url,
            'map'               => \Locator\Mapper::getMapper()->showMap($this->lat, $this->lng, $info_window),
            'adblock'           => PLG_displayAdBlock('locator_marker', 0),
            'show_map'          => true,
            'directions'        => \Locator\Mapper::getMapper()->showDirectionsForm($this->lat, $this->lng),
            'distFromOrigin'    => $this->distFromOrigin,
            'dist_unit'         => $_CONF_GEO['distance_unit'],
        ) );

        // Show the location's weather if that plugin integration is enabled
        if ($_CONF_GEO['use_weather']) {
            // Try coordinates first, if present
            if (!empty($this->lat) && !empty($this->lng)) {
                $loc = array(
                    'type' => 'coord',
                    'parts' => array(
                        'lat' => $this->lat,
                        'lng' => $this->lng,
                    ),
                );
            } else {
                // The postal code works best, but not internationally.
                // Try the regular address first.
                if (!empty($city) && !empty($province)) {
                    $loc = array(
                        'type' => 'address',
                        'parts' => array(
                            'city' => $this->city,
                            'province' => $this->state,
                            'country' => $this->country,
                        ),
                    );
                }
                if (!empty($postal)) {
                    $loc['parts']['postal'] = $postal;
                }
            }
            $args = array('loc' => $loc);
            /*if ($this->lat != 0 && $this->lng != 0) {
                $args = array('loc' => $this->lat . ',' . $this->lng);
            } else {
                $args = array('loc' => $this->address);
            }*/
            $s = LGLIB_invokeService(
                'weather', 'embed',
                $args, $weather, $svc_msg
            );
            if ($s == PLG_RET_OK) {
                $T->set_var('weather', $weather);
            }
        }

        $T->parse('output', 'page');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }


    /**
     * Toggles a boolean field based on the current value.
     * Current value must be provided.
     *
     * @param   string  $id         ID number of element to modify
     * @param   string  $field      Name of field to modify
     * @param   integer $oldvalue   Original value
     * @return          New value, or old value upon failure
     */
    public static function Toggle($id, $field, $oldvalue)
    {
        global $_TABLES;

        // Sanitize the current value
        $oldvalue = $oldvalue == 1 ? 1 : 0;
        $retval = $oldvalue;

        // Only act on valid fields
        switch ($field) {
        case 'is_origin':
        case 'enabled':
            // Set the new value
            $newvalue = $oldvalue == 1 ? 0 : 1;
            $id = COM_sanitizeID($id);
            $sql = "UPDATE {$_TABLES['locator_markers']}
                    SET $field = $newvalue
                    WHERE id='$id'";
            DB_query($sql, 1);
            if (DB_error()) {
                COM_errorLog("Marker::Toggle() failed. SQL: $sql");
                $retval = $oldvalue;
            } else {
                $retval = $newvalue;
            }
        }
        return $retval;
    }


    /**
     * Combine the address elements into a string to be used for geocoding.
     * Override the delimiter to create a different format.
     *
     * @param   string  $delim  Delimiter, default to comma
     * @return  string  String form of address
     */
    public function AddressToString($delim = ', ')
    {
        $parts = array();
        foreach (array('address', 'city', 'state', 'postal') as $fld) {
            if ($this->$fld != '') {
                $parts[] = $this->$fld;
            }
        }
        return implode($delim, $parts);
    }


    /**
     * Combine the address elements into a string to be used for geocoding.
     * Override the delimiter to create a different format.
     *
     * @param   string  $delim  Delimiter, default to comma
     * @return  string  String form of address
     */
    public function AddressToHTML()
    {
        $retval = '';
        if ($this->address != '') {
            $retval .= $this->address . '<br />';
        }
        if ($this->city != '') {
            $retval .= $this->city;
            if ($this->state != '') {
                $retval .= ', ';
            }
        }
        if ($this->state != '') {
            $retval .= $this->state;
            if ($this->postal != '') {
                $retval .= ' ';
            }
        }
        if ($this->postal != '') {
            $retval .= $this->postal;
        }
        return $retval;
    }


    /**
     * Builds an admin list of locations.
     *
     * @return  string HTML for the location list
     */
    public static function adminList()
    {
        global $_TABLES, $LANG_ADMIN, $LANG_GEO;

        USES_lib_admin();

        $retval = '';
        $header_arr = array(      # display 'text' and use table field 'field'
            array(
                'text' => $LANG_ADMIN['edit'],
                'field' => 'edit',
                'sort' => false,
                'align' => 'center',
            ),
            array(
                'text' => 'ID',
                'field' => 'id',
                'sort' => true,
            ),
            array(
                'text' => $LANG_GEO['title'],
                'field' => 'title',
                'sort' => true,
            ),
            array(
                'text' => $LANG_GEO['address'],
                'field' => 'address',
                'sort' => true,
            ),
            array(
                'text' => $LANG_GEO['origin'],
                'field' => 'is_origin',
                'sort' => true,
                'align' => 'center',
            ),
            array(
                'text' => $LANG_GEO['enabled'],
                'field' => 'enabled',
                'sort' => true,
                'align' => 'center',
            ),
            array(
                'text' => $LANG_GEO['latitude'],
                'field' => 'lat',
                'sort' => true,
            ),
            array(
                'text' => $LANG_GEO['longitude'],
                'field' => 'lng',
                'sort' => true,
            ),
            array(
                'text' => $LANG_ADMIN['delete'],
                'field' => 'deletemarker',
                'sort' => false,
                'align' => 'center',
            ),
        );
        $defsort_arr = array('field' => 'title', 'direction' => 'asc');
        $text_arr = array(
            'has_extras' => true,
            'form_url' => LOCATOR_ADMIN_URL . '/index.php',
        );

        $query_arr = array(
            'table' => 'locator_markers',
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

        $retval .= COM_createLink(
            $LANG_GEO['contrib_origin'],
            LOCATOR_ADMIN_URL . '/index.php?edit=x',
            array(
                'class' => 'uk-button uk-button-success',
                'style' => 'float:left',
            )
        );
        $retval .= ADMIN_list(
            'locator',
            array(__CLASS__, 'getAdminField'),
            $header_arr,
            $text_arr, $query_arr, $defsort_arr, '', '',
            $options_arr, $form_arr
        );
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
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
    {
        global $_CONF, $_CONF_GEO, $LANG_GEO, $LANG_ADMIN;

        $retval = '';

        switch($fieldname) {
        case 'edit':
        case 'edituserloc':
            $retval = COM_createLink('',
                LOCATOR_ADMIN_URL . '/index.php?' . $fieldname . '=x&amp;id=' .$A['id'],
                array(
                    'class' => 'uk-icon uk-icon-edit'
                )
            );
            break;

        case 'deletemarker':
        case 'deleteuserloc':
            $retval = COM_createLink('',
                LOCATOR_ADMIN_URL . '/index.php?' . $fieldname . '=x&amp;id=' . $A['id'],
                array(
                    'title' => $LANG_ADMIN['delete'],
                    'onclick'=>"return confirm('{$LANG_GEO['confirm_delitem']}');",
                    'class' => 'uk-icon uk-icon-trash loc-icon-danger'
                )
            );
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
     * Actually performs the search for location within $radius $units of $lat/$lng.
     *
     * @param  integer $radius Radius from origin to include
     * @param  string  $units  Unit of measure for radius.  'km' or 'miles'
     * @param  string  $keywords   Search keywords to limit results
     * @return array   Array of location records matching criteria
     */
    public function getNearby($radius, $units='', $keywords='')
    {
        global $_TABLES, $_CONF_GEO, $_USER;

        if ($units == '') {
            $units = $_CONF_GEO['distance_unit'];
        }
        if ($units == 'km') {
            $factor = 6371;
        } else {
            $factor = 3959;
        }

        $radius = (int)$radius;
        $values = array();
        if ($this->getLat() == 0 || $this->getLng() == 0) {
            // If invalid coordinates, return empty array
            return $values;
        }

        // Replace commas in lat & lng with decimal points
        $lat = GEO_coord2str($this->getLat(), true);
        $lng = GEO_coord2str($this->getLng(), true);

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
        if (!$result) {
            return "Error reading from database";
        }

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
    public static function originSelect($id)
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
            $userloc = DB_getItem(
                $_TABLES['userinfo'],
                'location',
                "uid=".$_USER['uid']
            );
            if ($userloc != '') {
                $retval .= "<option value=\"user\" $selected>{$userloc}</option>\n";
            }
        } else {
            // Get the systemwide origins
            $retval = COM_optionList(
                $_TABLES['locator_markers'],
                'id,title',
                $id,
                1,
                "is_origin=1 or id='$id'"
            );
        }
        return $retval;
    }


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
    public static function getLocations($id, $radius=0, $units='', $keywords='', $address='')
    {
        global $_TABLES, $_CONF_GEO, $_CONF, $_USER, $LANG_GEO;

        $content = '';
        $locations = array();
        $errmsg = '';

        if ($units == '') {
            $units = $_CONF_GEO['distance_unit'];
        }
        if ($radius == 0) {
            $radius = $_CONF_GEO['default_radius'];
        }

        $url_opts = '&origin=' . urlencode($id).
            '&radius=' . (int)$radius.
            '&units=' . urlencode($units).
            '&keywords=' . urlencode($keywords).
            '&address=' . urlencode($address);

        $T = new \Template($_CONF['path'] . 'plugins/locator/templates');
        $T->set_file('page', 'loclist.thtml');
        $T->set_var(array(
            'action_url'    => $_SERVER['PHP_SELF'],
            'origin_select' => self::originSelect($id),
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
            COM_clearSpeedlimit(
                $_CONF['speedlimit'],
                $_CONF_GEO['pi_name'].'lookup'
            );
            $last = COM_checkSpeedlimit($_CONF_GEO['pi_name'].'lookup');
            if ($last > 0) {
                $errmsg = $LANG_GEO['speedlimit_exceeded'];
            } elseif (Locator\Mapper::getGeocoder()->geoCode($address, $lat, $lng)) {
                $M = new self;
                $locations = $M->setLat($lat)
                    ->setLng($lng)
                    ->getNearby($radius, $units, $keywords);
                COM_updateSpeedlimit($_CONF_GEO['pi_name'].'lookup');
            }
        } elseif ($id != 'user') {
            // Get all locations within $radius
            $M = new self($id);
            $locations = $M->getNearby($radius, $units, $keywords);
        } elseif (!COM_isAnonUser()) {
            // use user profile location
            $user_location = DB_getItem(
                $_TABLES['userinfo'],
                'location',
                'uid='. $_USER['uid']
            );
            $userloc = new UserLoc($user_location);
            if ($userloc->getLat() != 0 && $userloc->getLng() != 0) {
                $M = new self;
                $locations = $M->setLat($userloc->getLat())
                    ->setLng($userloc->getLng())
                    ->getNearby($radius, $units, $keywords);
                /*$locations = getLocsByCoord(
                    $userloc->getLat(), $userloc->getLng(), $radius,
                    $units, $keywords
                );*/
            }
        }

        for ($i = 0; $i < count($locations); $i++) {
            // The origin is in this array, so skip it
            if ($locations[$i]['id'] == $id) continue;

            $T->set_block('page', 'LocRow', 'LRow');
            if ($locations[$i]['is_origin'] == 0) {
                $T->set_var('loc_url', LOCATOR_URL .
                    '/index.php?ddorigin=x&origin=' .
                    $locations[$i]['id'] . '&id=' . $locations[$i]['id'] .
                    '&radius=' . $radius);
            }
            $dist = sprintf("%4.2f", $locations[$i]['distance']);
            $T->set_var(array(
                'loc_id'    => $locations[$i]['id'],
                'loc_name'  => $locations[$i]['title'],
                'loc_address' => $locations[$i]['address'],
                'loc_distance' => $dist,
                'loc_info_url' => LOCATOR_URL .
                                '/index.php?detail=x&id=' .
                                $locations[$i]['id'] . $url_opts .
                                '&dist=' . $dist,
                'loc_lat'   => $locations[$i]['lat'],
                'loc_lng'   => $locations[$i]['lng'],
                'url_opts'  => $url_opts,
                'adblock'   => PLG_displayAdBlock('locator_list', $i+1),
            ) );
            if (
                $locations[$i]['is_origin'] == 1 ||
                $locations[$i]['userOrigin'] != NULL
            ) {
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

        if ($i == 0) {
            if ($errmsg == '') {
                $errmsg = $LANG_GEO['no_locs_found'];
            }
            $T->set_var('no_display', $errmsg);
        }

        $T->parse('output','page');
        $content .= $T->finish($T->get_var('output'));
        return $content;
    }

}

?>
