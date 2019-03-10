<?php
/**
 * User location class for the Locator plugin.
 * The UserLoc class handles user locations based on each user's profile.
 * The UserOrigin class is for addresses entered by users as search origins,
 * and are subject to being purged after some time.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2011 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.0.2
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
    /**
     * Properties.
     * Accessed via `__set()` and `__get()` functions
     * @var array */
    var $properties = array();


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

        if ($uid == 0) $uid = (int)$_USER['uid'];
        $this->id = 0;
        $this->lat = 0;
        $this->lng = 0;
        $this->type = 0;

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
     * Magic setter function.
     * Sets the property value for the named key.
     *
     * @param   string  $key    Name of property to store
     * @param   mixed   $value  Value to save for property
     */
    public function __set($key, $value)
    {
        switch ($key) {
        case 'id':
        case 'type':    // only 0 or 1 now, but may expand types later
            $this->properties[$key] = (int)$value;
            break;

        case 'lat':
        case 'lng':
            $this->properties[$key] = (float)$value;
            break;

        case 'location':
            $value = trim(COM_checkWords(COM_checkHTML($value)));
            $this->properties[$key] = $value;
            break;
        }
    }


    /**
     * Magic getter function.
     * Returns the property associated with the provided key.
     *
     * @param   string  $key    Name of property to return
     * @return  mixed           Value of named property, or NULL if undefined
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
            $this->id = $row['id'];
            $this->lat = $row['lat'];
            $this->lng = $row['lng'];
            $this->location = $row['location'];
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
     * @return  integer Google return code, or false for failure.
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
        Mapper::getGeocoder()->geoCode($address, $lat, $lng);
        $this->lat = $lat;
        $this->lng = $lng;
    }

}   // class UserLoc

?>
