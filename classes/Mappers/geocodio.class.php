<?php
/**
 * Class for Geocodio Geocoding provider.
 * https://www.geocodio.io
 * This provides geocoding only, no map generation.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.2.0
 * @since       1.2.0
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Locator\Mappers;

/**
 * Class to support geocoding by geocodio.com.
 * @package locator
 */
class geocodio extends \Locator\Mapper
{
    /** API key from service provider.
     * @var string */
    private $api_key = NULL;

    /** Indicate that this service provides geocoding.
     * @var boolean */
    protected $is_geocoder = true;

    /** Display name for this service.
     * @var string */
    protected $display_name = 'Geocodio';

    /** Internal name for this service.
     * @var string */
    protected $name = 'geocodio';

    /** URL to geocoding service.
     * @var string */
    const GEOCODE_URL = 'https://api.geocod.io/v1.3/geocode?api_key=%s&q=%s';


    /**
     * Constructor.
     *
     * @param   string  $id     Optional ID of a location to load
     */
    public function __construct($id = '')
    {
        global $_CONF_GEO;
        if (isset($_CONF_GEO['geocodio_api_key'])) {
            $this->api_key = $_CONF_GEO['geocodio_api_key'];
        }
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
            if (empty($this->api_key)) {
                COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . '():  API Key is required');
                return -1;
            }
            $url = sprintf(self::GEOCODE_URL, $this->api_key, urlencode($address));
            $json = self::getUrl($url);
            $data = json_decode($json, true);
            if (!isset($data['results'][0]['location']) || empty($data['results'][0]['location'])) {
                // Didn't get even one result
                return -1;
            }
            \Locator\Cache::set($cache_key, $data);
        }

        // Get the most accurate result
        $acc_code = -1;     // Initialize accuracy code
        foreach ($data['results'] as $idx=>$loc_data) {
            $loc_acc_code = (float)$loc_data['accuracy'];
            if ($loc_acc_code > $acc_code) {
                $acc_code = $loc_acc_code;
                $loc_idx = $idx;
            }
        }
        $loc = $data['results'][$idx]['location'];

        if (!isset($loc['lat']) || !isset($loc['lng'])) {
            $lat = 0;
            $lng = 0;
            return -1;
        } else {
            $lat = $loc['lat'];
            $lng = $loc['lng'];
            return 0;
        }
    }

}

?>
