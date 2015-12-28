<?php
/**
*   User location class for the Locator plugin
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2009-2011 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    1.0.2
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

/**
*   Class to handle the general location markers
*   @package    locator
*/
class Marker
{
    private $isAdmin = false;
    private $properties = array();


    /**
    *   Constructor
    *
    *   @param  string  $id     Optional ID of a location to load
    */
    public function __construct($id = '')
    {
        global $_CONF_GEO, $_USER;

        $this->id = $id;
        if ($this->id != '') {
            $this->Read();
        } else {
            $this->title = '';
            $this->description = '';
            $this->radius = 0;
            $this->keywords = '';
            $this->address = '';
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
    *   Magic setter function.
    *
    *   @param  string  $key    Name of variable to set
    *   @param  mixed   $value  Value to set
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
    *   Magic getter function
    *
    *   @param  string  $key    Name of variable to get
    *   @return mixed           Value of $key, if set, or NULL
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
    *   Read a marker from the database into variables
    *
    *   @param  string  $id     Optional ID of marker, or current is used
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
        $this->SetVars($A, true);
    }


    /**
    *   Set all variables from the database or form into the object
    *
    *   @param  array   $A      Array of name=>value pairs
    *   @param  boolean $fromDB TRUE if $A is from the DB, FALSE if a form
    */
    public function SetVars($A, $fromDB=false)
    {
        if (empty($A) || !is_array($A)) return false;

        $this->id = $A['id'];
        $this->lat = $A['lat'];
        $this->lng = $A['lng'];
        $this->keywords = $A['keywords'];
        $this->address = $A['address'];
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
    *   Delete a single marker, and all category assignments
    *
    *   @param  array   $id ID of marker to delete
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
    *   Updates an existing marker in either the live or submission table
    *
    *   @param  array   $A      Form data
    *   @param  string  $table  Table to update
    */
    public function Save($A, $table='locator_markers')
    {
        global $_TABLES, $_USER, $_CONF_GEO;

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
        // from Google
        $lat = $this->lat;      // convert to "real" variables
        $lng = $this->lng;      // so the pointer can be passed
        if ( (empty($lat) == '' || empty($lng))
                && $this->address != '' 
                && $_CONF_GEO['autofill_coord'] == true ) {
            if (GEO_getCoords($this->address, $lat, $lng) == 0) {
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

        // Fix commas used as decimals
        $lat = number_format($this->lat, 8, '.', '');
        $lng = number_format($this->lng, 8, '.', '');

        $sql1 = "title = '" . DB_escapeString($this->title) . "',
            address = '" . DB_escapeString($this->address) . "',
            description = '" . DB_escapeString($this->description) . "',
            lat = '$lat',
            lng = '$lng',
            keywords = '" . DB_escapeString($this->keywords) . "',
            url = '" . DB_escapeString(COM_sanitizeUrl($A['url'])) . "',
            is_origin = '{$this->is_origin}',
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
            // like to report accurately to the user.  Check both the 
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
        if (DB_error())
            //return DB_error();
            return 99;
        else 
            return 0;
    }


    /**
    *   Display the marker edit form
    *
    *   @param  string  $id     Optional ID to load & edit, current if empty
    *   @param  string  $mode   Optional mode indicator to set form action
    */
    public function Edit($id = '', $mode='submit')
    {
        global $_CONF_GEO, $_TABLES, $_CONF, $LANG24, $LANG_postmodes, $_SYSTEM;

        if ($id != '') {
            $this->Read($id);
        } elseif ($this->id == '') {
            $this->id = COM_makeSid();
        }

        //displays the add quote form for single quotations
        $T = new Template(LOCATOR_PI_PATH . '/templates');
        $T->set_file('page', "markerform.thtml");

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
            'lat'           => $this->lat,
            'lng'           => $this->lng,
            'keywords'      => $this->keywords,
            'url'           => $this->url,
            'origin_chk'    => $this->is_origin == 1 ? 'checked="checked"' : '',
            'enabled_chk'   => $this->enabled == 1 ? 'checked="checked"' : '',
            'post_options'  => $post_options,
            'permissions_editor' => SEC_getPermissionsHTML(
                            $this->perm_owner, $this->perm_group,
                            $this->perm_members, $this->perm_anon),
            'pi_name'       => $_CONF_GEO['pi_name'],
            'action'        => $mode,
            'mootools' => $_SYSTEM['disable_mootools'] ? '' : 'true',
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

        $T->set_file('page', 'closemarkerform.thtml');
        $T->set_var(array(
            //'submission_option' => $sub_type,
            'saveaction'     => $saveaction,
            //'cancel_url'    => $cancel_url,
        ) );

        $T->parse('output','page');
        $retval .= $T->finish($T->get_var('output'));

        return $retval;
    }


    /**
    *   Increment the hit counter for a marker
    *
    *   @param  string  $id     Optional marker ID, current marker if empty
    */
    public function Hit($id = '')
    {
        global $_TABLES;

        if ($id == '' && is_object($this))
            $id = $this->id;

        if (empty($id)) return;     // Still empty? Nothing to do

        DB_Query("UPDATE {$_TABLES['locator_markers']} 
            SET views = views + 1
            WHERE id = '" . COM_sanitizeId($id) . "'");
    }


    /**
    *   Displays the location's information, along with a map.
    *   May be expanded in the future to use $origin to create driving 
    *   directions.
    *
    *   @param  string  $origin Optional origin ID, used to create directions
    *   @return string  HTML displaying location with map
    */
    public function Detail($origin='')
    {
        global $_CONF, $_CONF_GEO;

        if ($this->id == '')
            return 'Error : ID is empty';

        //$origin= COM_sanitizeID($origin);
        $srchval = isset($_GET['query']) ? trim($_GET['query']) : '';

        $this->Hit();

        // Highlight search terms, if any
        if ($srchval != '') {
            $title = COM_highlightQuery($this->title, $srchval);
            $description = COM_highlightQuery($this->description, $srchval);
            $address = COM_highlightQuery($this->address, $srchval);
            // Don't do the url, the quotes get messed up.
        } else {
            $title = $this->title;
            $description = $this->description;
            $address = $this->address;
        }

        $T = new Template(LOCATOR_PI_PATH . '/templates');
        $T->set_file('page', 'locinfo.thtml');

        if ($this->isAdmin) {
            $admin_options = COM_createLink( 
                COM_createImage($_CONF['layout_url'] . '/images/edit.png'),
                LOCATOR_ADMIN_URL . '/index.php?edit=x&id=' . $this->id);
        } else {
            $admin_options = '';
        }

        $T->set_var(array(
            'admin_options'     => $admin_options,
            'action_url'        => $_SERVER['PHP_SELF'],
            'name'              => $title,
            'address'           => $address,
            'description'       => $description,
            'url'               => COM_createLink($this->url, $this->url, 
                                    array('target' => '_new')),
            'lat'               => number_format($this->lat, 8, '.', ''),
            'lng'               => number_format($this->lng, 8, '.', ''),
        ) );
        /*if ($origin != '')
            $T->set_var('origin_addr', 
            DB_getItem($_TABLES['locator_markers'], 'address', "id='$origin'"));
        */
        if (    $_CONF_GEO['show_map']
                && $this->lat != 0 && $this->lng != 0
        ) {
            // Try to get the Google map
            list($js_url, $canvas_id) = GEO_MapJS();
            $T->set_var(array(
                'show_map'          => 'true',
                'infowindow_text'   => htmlspecialchars($this->title) . 
                        '<br ' . XHTML . '>' . htmlspecialchars($this->address),
                'geo_map_js_url'    => $js_url,
                'canvas_id'         => $canvas_id,
             ) );
        } else {
            // Can't show the map if either coordinate is empty
            $T->clear_var('show_map');
        }

        // Show the location's weather if that plugin integration is enabled
        if ($_CONF_GEO['use_weather']) {
            $args = array(
                'loc'   => $this->address,
                'lat'   => $this->lat,
                'lng'   => $this->lng,
            );
            $s = GEO_invokeService('weather', 'embed', $args, $weather, $svc_msg);
            if ($s == PLG_RET_OK) {
                $T->set_var('weather', $weather);
            }
        }
 
        if ($_CONF_GEO['use_directions']) {
            $T->set_var('directions', 'true');
        }
 
        $T->parse('output', 'page');
        $retval .= $T->finish($T->get_var('output')); 

        return $retval;
    }

}

?>
