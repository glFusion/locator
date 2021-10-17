<?php
/**
 * Class for U.S. Census Geocoding provider.
 * This provides geocoding only, no map generation.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018-2021 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.2.2
 * @since       1.2.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Locator\Mappers;

/**
 * Provides geocoding service from the U.S. Census. Does not provide mapping.
 * @since   version 1.2.0
 * @package locator
 */
class uscensus extends \Locator\Mapper
{
    /** Indicate that this service provides geocoding.
     * @var boolean */
    protected $is_geocoder = true;

    /** Display name for this service.
     * @var string */
    protected $display_name = 'US Census';

    /** Class name for this service.
     * @var string */
    protected $name = 'uscensus';

    /** Geocoding URL for this service.
     * @const string */
    const GEOCODE_URL = 'https://geocoding.geo.census.gov/geocoder/locations/onelineaddress?benchmark=9&format=json&address=%s';

    /**
     * Constructor. Nothing to do for this mapper.
     *
     * @param   string  $id     Optional ID of a location to load
     */
    public function __construct($id = '')
    {
    }


    /**
     * Get the coordinates from an address string.
     *
     * @param   string  $address    Address string
     * @param   float   &$lat       Latitude return var
     * @param   float   &$lng       Longitude return var
     * @return  integer             0 for success, nonzero for failure
     */
    public function geoCode($address, &$lat, &$lng)
    {
        $cache_key = $this->getName() . '_geocode_' . md5($address);
        $data = \Locator\Cache::get($cache_key);
        if ($data === NULL) {
            $url = sprintf(self::GEOCODE_URL, urlencode($address));
            $json = self::getUrl($url);
            $data = json_decode($json, true);
            if (!isset($data['result']['addressMatches']) || empty($data['result']['addressMatches'])) {
                return -1;
            }

            \Locator\Cache::set($cache_key, $data);
        }
        $loc = $data['result']['addressMatches'][0]['coordinates'];

        if (!isset($loc['x']) || !isset($loc['y'])) {
            $lat = 0;
            $lng = 0;
            return -1;
        } else {
            $lat = $loc['y'];
            $lng = $loc['x'];
            return 0;
        }
    }

}

?>
