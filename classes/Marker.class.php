<?php
/**
 * Marker class for the Locator plugin
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2018 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.2.0
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
    /** Indicate that the current user is a plugin administrator.
     * @var boolean */
    private $isAdmin = false;

    /** Marker properties, accessed via `__set()` and `--get()`.
     * @var array */
    private $properties = array();

    /** Flag to indicate a new marker vs. one read from the DB.
     * @var boolean */
    public $isNew = true;


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
            $this->title = '';
            $this->description = '';
            $this->radius = 0;
            $this->keywords = '';
            $this->address = '';
            $this->city = '';
            $this->state = '';
            $this->postal = '';
            $this->oldid = '';
            $this->enabled = 1;
            $this->is_origin = 0;
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
     * Set a property's value.
     *
     * @param   string  $key    Name of variable to set
     * @param   mixed   $value  Value to set
     */
    public function __set($key, $value)
    {
        global $_CONF_GEO;

        switch ($key) {
        case 'id':
        case 'oldid':       // used during update of existing
            if (!empty($value)) {
                $this->properties[$key] = COM_sanitizeId($value);
            } else {
                $this->properties[$key] = '';
            }
            break;

        case 'lat':
        case 'lng':
            // Convert European decimal char if coming from a form
            $value = str_replace(',', '.', $value);
            $this->properties[$key] = (float)$value;
            break;

        case 'views':
        case 'add_date':
        case 'perm_owner':
        case 'perm_group':
        case 'perm_members':
        case 'perm_anon':
        case 'owner_id':
        case 'group_id':
            $this->properties[$key] = (int)$value;
            break;

        case 'is_origin':
        case 'enabled':
            $this->properties[$key] = $value == 1 ? 1 : 0;
            break;

        case 'address':
        case 'city':
        case 'state':
        case 'postal':
        case 'keywords':
        case 'description':
        case 'title':
        case 'url':
            $value = trim(COM_checkWords(COM_checkHTML($value)));
            $this->properties[$key] = $value;
            break;

        case 'radius':
            $this->properties[$key] =
                $value == 0 ? $_CONF_GEO['default_radius'] : (int)$value;
            break;
        }
    }


    /**
     * Get the value of a property, or NULL if not defined.
     *
     * @param   string  $key    Name of variable to get
     * @return  mixed           Value of $key, if set, or NULL
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->properties)) {
            return $this->properties[$key];
        } else {
            return NULL;
        }
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
        if (!is_array($A) || empty($A))
            return 0;

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
        if ( (empty($lat) || empty($lng))
            && $_CONF_GEO['autofill_coord'] == true ) {
            $address = $this->AddressToString();
            if ($address != '' && GEO_getCoords($address, $lat, $lng) == 0) {
                $this->lat = $lat;
                $this->lng = $lng;
            }
        }

        if ($this->id == '') {
            if ($this->oldid != '')
                $this->id = $this->oldid;
            else
                $this->id = COM_makeSid();
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
            'mootools' => $_SYSTEM['disable_mootools'] ? '' : 'true',
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
            'address'           => $this->address,
            'city'              => $this->city,
            'state'             => $this->state,
            'postal'            => $this->postal,
            'description'       => $this->description,
            'url'               => COM_createLink($this->url, $this->url,
                                    array('target' => '_new')),
            'lat'               => GEO_coord2str($this->lat),
            'lng'               => GEO_coord2str($this->lng),
            //'back_url'          => $back_url,
            'map'               => \Locator\Mapper::getMapper()->showMap($this->lat, $this->lng, $info_window),
            'adblock'           => PLG_displayAdBlock('locator_marker', 0),
            'show_map'          => true,
            'directions'        => \Locator\Mapper::getMapper()->showDirectionsForm($this->lat, $this->lng),
        ) );

        // Show the location's weather if that plugin integration is enabled
        if ($_CONF_GEO['use_weather']) {
            if ($this->lat != 0 && $this->lng != 0) {
                $args = array('loc' => $this->lat . ',' . $this->lng);
            } else {
                $args = array('loc' => $this->address);
            }
            $s = LGLIB_invokeService('weather', 'embed', $args, $weather, $svc_msg);
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

}

?>
