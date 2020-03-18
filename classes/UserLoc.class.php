<?php
/**
 * User location class for the Locator plugin.
 * The UserLoc class handles user locations based on each user's profile.
 * The UserOrigin class is for addresses entered by users as search origins,
 * and are subject to being purged after some time.
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
 * Class to handle the user's location from the glFusion profile.
 * @package locator
 */
class UserLoc
{
    /** Location record ID.
     * @var integer */
    private $id = 0;

    /** Location type, 1 if origin, 0 if not.
     * @var integer */
    private $type = 0;

    /** Location latitude.
     * @var float */
    private $lat = 0;

    /** Location longitude.
     * @var float */
    private $lng = 0;

    /** Location address.
     * @var string */
    private $location;


    /**
     * Constructor.
     * Set variables, and read from the database if a location id
     * is passed in.
     *
     * @uses   self::readFromDB()
     * @uses   self::getCoords()
     * @uses   self::saveToDB()
     * @param  integer  $location   Location ID to read from DB (optional)
     * @param  integer  $uid        Optional User ID, current user by default
     */
    public function __construct($location = '', $uid = 0)
    {
        global $_USER;

        if ($uid == 0) $this->uid = (int)$_USER['uid'];
        $this->uid = (int)$uid;

        // Get the user's location from the DB. If it doesn't exist, get its
        // coordinates.
        if (!$this->readFromDB()) {
            $this->location = $location;
            $this->getCoords();
            $this->saveToDB();
        } else {
            // Found a record, see if it's the same location
            if ($location != $this->location) {
                $this->location = $location;
                $this->getCoords();
            } elseif ($this->lat == 0 || $this->lng == 0) {
                // Record had zero coordinates, possibly due to an error.
                // Re-geocode the address.
                $this->getCoords();
            }
        }
    }


    /**
     * Get a user location by ID.
     *
     * @param   integer $id     Location record ID
     * @return  object      UserLoc object
     */
    public static function getByID($id)
    {
        global $_TABLES;

        $obj = new self;
        $sql = "SELECT * FROM {$_TABLES['locator_userloc']}
            WHERE id = " . (int)$id;
        $res = DB_query($sql);
        if (DB_numRows($res) == 1) {
            $obj->setVars(DB_fetchArray($res, false));
        }
        return $obj;
    }


    /**
     * Set local property values.
     *
     * @param   array   $A      Array of properties
     * @return  object  $this
     */
    public function setVars($A)
    {
        $this->id = (int)$A['id'];
        $this->lat = (float)$A['lat'];
        $this->lng = (float)$A['lng'];
        $this->location = $A['location'];
        return $this;
    }


    /**
     * Get the latitude for the location.
     *
     * @return  float       Latitude value
     */
    public function getLat()
    {
        return (float)$this->lat;
    }


    /**
     * Get the longitude for the location.
     *
     * @return  float       Longitude value
     */
    public function getLng()
    {
        return (float)$this->lng;
    }


    /**
     * Read the current user's location from the database.
     * There is only one profile location per user.
     *
     * @return  boolean     True on success, False on failure or not found
     */
    public function readFromDB()
    {
        global $_TABLES, $_USER;

        $sql = "SELECT * from {$_TABLES['locator_userloc']}
                WHERE uid = " . (int)$_USER['uid'] .
                " AND type = {$this->type}";
        //echo $sql;die;
        $result = DB_query($sql);
        if ($result && DB_numRows($result) > 0) {
            $row = DB_fetchArray($result, false);
            $this->setVars($row);
            return true;
        } else {
            return false;
        }
    }


    /**
     * Save the current variables to the database.
     * The update portion is here for completeness and future use,
     * but should not currently be needed as there is no editing function.
     */
    public function saveToDB()
    {
        global $_TABLES, $_USER;

        if ($this->id == 0) {
            $sql1 = "INSERT INTO {$_TABLES['locator_userloc']} SET
                    type = {$this->type},
                    uid = " . (int)$_USER['uid'] . ", ";
            $sql3 = '';
        } else {    // For completeness, shouldn't be called.
            $sql1 = "UPDATE {$_TABLES['locator_userloc']} SET ";
            $sql3 = " WHERE id={$this->id}";
        }

        // Force decimal formatting in case locale is different
        $lat = GEO_coord2str($this->lat, true);
        $lng = GEO_coord2str($this->lng, true);

        $sql2 = "location = '" . DB_escapeString($this->location) . "',
                lat = '{$lat}',
                lng = '{$lng}'";
        //COM_errorLog($sql1.$sql2.$sql3);
        DB_query($sql1.$sql2.$sql3, 1);
        if (!DB_error()) {
            if ($this->id == 0) {
                $this->id = DB_insertId();
            }
            return true;
        } else {
            COM_errorLog("Error updating userloc table: $sql");
            return false;
        }
    }


    /**
     * Retrieve the coordinates for the current location.
     * Sets the local $lat and $lng variables
     *
     * @uses    Marker::geoCode()
     * @return  object  $this
     */
    public function getCoords()
    {
        global $_CONF_GEO;

        // Check for valid Google config items, and if we're configured
        // to automatically geocode addresses
        if ($_CONF_GEO['autofill_coord'] == 0) {
            return 0;
        }

        // Use local variables to allow pass-by-reference
        $lat = 0;
        $lng = 0;
        Mapper::getGeocoder()->geoCode($this->location, $lat, $lng);
        $this->lat = $lat;
        $this->lng = $lng;
        return $this;
    }


    /**
     * Creates an administrator list to allow users to add origins to
     * their preferences.
     *
     * @return  string HTML of origin list
     */
    public static function originList()
    {
        global $_CONF, $_TABLES, $_CONF_GEO, $LANG_GEO, $_USER;

        if (COM_isAnonUser()) return '';

        USES_lib_admin();

        $retval = '';

        $header_arr = array(      # display 'text' and use table field 'field'
            array(
                'text' => 'ID',
                'field' => 'id',
                'sort' => true,
            ),
            array(
                'text' => $LANG_GEO['address'],
                'field' => 'location',
                'sort' => true,
            ),
            array(
                'text' => $LANG_GEO['latitude'],
                'field' => 'lat',
                'sort' => true,
                'align' => 'right',
            ),
            array(
                'text' => $LANG_GEO['longitude'],
                'field' => 'lng',
                'sort' => true,
                'align' => 'right',
            ),
        );
        $defsort_arr = array('field' => 'location', 'direction' => 'asc');
        $text_arr = array(
            'has_extras' => true,
            'form_url' => LOCATOR_URL . '/index.php?page=myorigins',
        );
        $form_arr = array();
        $query_arr = array(
            'table' => 'locator_userloc',
            'sql' => "SELECT * FROM {$_TABLES['locator_userloc']}",
            'query_fields' => array('location'),
            'default_filter' => "WHERE uid={$_USER['uid']}",
        );

        $retval .= COM_startBlock(
            $LANG_GEO['admin_menu'],
            '',
            COM_getBlockTemplate('_admin_block', 'header')
        );
        $retval .= ADMIN_list(
            'locator',
            array(__NAMESPACE__ . '\Marker', 'getAdminField'),
            $header_arr,
            $text_arr, $query_arr, $defsort_arr,
            '', '', '', $form_arr
        );
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
        return $retval;
    }


    /**
    *   Displays the edit form for user locations.
    *   Only admins have access to this form.
    *
    *   @param  array   $A  Array returned from a DB lookup, or empty string.
    *   @return string      HTML for the user location editing form.
    */
    public function Edit()
    {
        global $_TABLES, $_CONF, $_USER, $LANG_GEO, $_CONF_GEO, $_SYSTEM;

        $retval = '';

        //displays the add quote form for single quotations
        $T = new \Template(
            $_CONF['path'] . 'plugins/' .  $_CONF_GEO['pi_name'] . '/templates'
        );
        $T->set_file('page', 'userlocform.thtml');
        $T->set_var(array(
            'location'  => $this->location,
            'lat'       => $this->lat,
            'lng'       => $this->lng,
            'frm_id'    => $this->id,
            'goog_map_instr' => $_CONF_GEO['autofill_coord'] != '' ?
                        $LANG_GEO['coord_instr2'] : '',
            'action_url' => $_CONF['site_admin_url']. '/plugins/'.
                    $_CONF_GEO['pi_name']. '/index.php',
            'pi_name'   => $_CONF_GEO['pi_name'],
            'action'    => 'saveuserloc',
            'show_del_btn' => !empty($this->id) ? 'true' : '',
        ) );
        $T->parse('output','page');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }


    /**
     * Creates an admin list to administer the user location cache.
     * The cache is built from the user locations given in glFusion account
     * settings.
     *
     * @return  string  HTML for the admin list
     */
    public static function adminList()
    {
        global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACCESS, $_CONF_GEO, $LANG_GEO;

        USES_lib_admin();

        $header_arr = array(
            /*array(
                'text' => $LANG_ADMIN['edit'],
                'field' => 'edituserloc',
                'sort' => false,
            ),*/
            array(
                'text' => $LANG_GEO['address'],
                'field' => 'location',
                'sort' => true,
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
                'field' => 'deleteuserloc',
                'sort' => false,
                'align' => 'center',
            ),
        );

        $defsort_arr = array('field' => 'location', 'direction' => 'asc');
        $text_arr = array(
            'has_extras' => true,
            'form_url' => LOCATOR_ADMIN_URL . '/index.php?userloc=x',
        );
        $query_arr = array(
            'table' => 'locator_userloc',
            'sql' => "SELECT * FROM {$_TABLES['locator_userloc']} ",
            'query_fields' => array(),
            'default_filter' => ''
        );
        $form_arr = array();
        return ADMIN_list(
            'locator',
            array(__NAMESPACE__ . '\Marker', 'getAdminField'),
            $header_arr,
            $text_arr, $query_arr, $defsort_arr, '', '', '', $form_arr
        );
    }

}   // class UserLoc

?>
