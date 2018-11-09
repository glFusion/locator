<?php
/**
 * User location class for the Locator plugin.
 * The UserLoc class handles user locations based on each user's profile.
 * The UserOrigin class is for addresses entered by users as search origins,
 * and are subject to being purged after some time.
 *
 * @author     Lee Garner <lee@leegarner.com>
 * @copyright  Copyright (c) 2009-2011 Lee Garner <lee@leegarner.com>
 * @package    locator
 * @version    1.0.2
 * @license    http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Locator;

/**
 * Class to handle the user-entered strings used as search origins.
 * Unlike user profile locations (type 0), these can be automatically
 * purged after some time.
 * @package locator
 */
class UserOrigin extends UserLoc
{
    /**
     * Constructor.
     * Calls the parent constructor and sets the record type to '1'
     *
     * @param   string  $location   Optional location to get immediately.
     */
    public function __construct($location='')
    {
        $this->location = $location;
        $this->id = 0;
        $this->lat = 0;
        $this->lng = 0;
        $this->type = 1;

        // If a location is supplied (it should be), try to read it
        // from the DB.  If it doesn't exist, get its coordinates and
        // save it for future use
        if ($this->location != '') {
            if (!$this->readFromDB()) {
                $this->getCoords();
                $this->saveToDB();
            }
        }
    }


    /**
     * Read the current location from the database
     *
     * @return  boolean     True on success, False on failure or not found
     */
    public function readFromDB()
    {
        global $_TABLES, $_USER;

        $sql = "SELECT * from {$_TABLES['locator_userloc']}
                WHERE location='" . DB_escapeString($this->location). "'
                AND uid = " . (int)$_USER['uid'];
        //echo $sql;die;
        $result = DB_query($sql);
        if ($result && DB_numRows($result) > 0) {
            $row = DB_fetchArray($result, false);
            $this->id = $row['id'];
            $this->lat = $row['lat'];
            $this->lng = $row['lng'];
            return true;
        } else {
            return false;
        }
    }
}

?>
