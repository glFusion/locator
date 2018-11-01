<?php
/**
 * Class for OpenStreetMap.org provider
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
*   Class to handle the general location markers
*   @package    locator
*/
class openstreetmap extends \Locator\Mapper
{
    protected $is_mapper = true;
    protected $is_geocoder = true;
    protected $display_name = 'OpenStreetMap';
    protected $name = 'openstreetmap';
    const GEOCODE_URL = 'https://nominatim.openstreetmap.org/search?format=json&q=%s';

    /**
    *   Constructor
    *
    *   @param  string  $id     Optional ID of a location to load
    */
    public function __construct($id = '')
    {
    }


    /**
     * Display a map.
     *
     * @param   float   $lat    Latitude
     * @param   float   $lng    Longitude
     * @param   string  $text   Optional text for marker
     * @return  string          HTML to generate the map
     */
    public function showMap($lat, $lng, $text = '', $tpl = 'small')
    {
        global $_CONF_GEO, $_CONF;

        // Insert a map, if configured correctly
        if ($_CONF_GEO['show_map'] == 0) {
            return '';
        }

        $lat = (float)$lat;
        $lng = (float)$lng;
        if ($lat == 0 || $lng == 0) {
            return '';
        }

        list($js_url, $canvas_id) = $this->getMapJS();
        $T = new \Template(LOCATOR_PI_PATH . '/templates/' . $this->getName());
        $T->set_file('page', $tpl . '_map.thtml');
        $T->set_var(array(
            'lat'           => GEO_coord2str($lat, true),
            'lng'           => GEO_coord2str($lng, true),
            'geo_map_js_url' => $js_url,
            'canvas_id'     => $canvas_id,
            'directions'    => $_CONF_GEO['use_directions'] ? true : false,
            'text'          => $text,
            'is_uikit'      => $_CONF_GEO['_is_uikit'],
            'x'     => '{x}',
            'y'     => '{y}',
            'z'     => '{z}',
            'id'    => '{id}',
        ) );
        $T->parse('output','page');
        return $T->finish($T->get_var('output'));
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
            if (!is_array($data) || !isset($data[0]['place_id'])) {
                COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . '(): Decoding Error - ' . $json);
                return -1;
            }
            \Locator\Cache::set($cache_key, $data);
        }
        // Get the most accurate result
        $acc_code = -1;     // Initialize accuracy code
        $loc = array();
        foreach ($data as $idx=>$loc_data) {
            $loc_acc_code = (float)$loc_data['importance'];
            if ($loc_acc_code > $acc_code) {
                $acc_code = $loc_acc_code;
                $loc = $loc_data;
            }
        }

        if (!isset($loc['lat']) || !isset($loc['lon'])) {
            $lat = 0;
            $lng = 0;
            return -1;
        } else {
            $lat = $loc['lat'];
            $lng = $loc['lon'];
            return 0;
        }
    }


    /**
     * Get the URL to JS and CSS for inclusion in a template.
     * This makes sure the javascript is included only once even if there
     * are multiple maps on the page.
     * Returns the URL, and a random number to be used for the canvas ID.
     *
     * @return  array   $url=>URL to javascript, $canvas_id=> random ID
     */
    private function getMapJS()
    {
        static $have_map_js = false;    // Flag to avoid duplicate loading

        $canvas_id = rand(1,999);   // Create a random id for the canvas
        if (!$have_map_js) {
            $have_map_js = true;
            $url = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css"
   integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA=="
   crossorigin=""/>' . LB;
            // JS must be included after CSS
            $url .= '<script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js"
   integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA=="
   crossorigin=""></script>' . LB;
        } else {
            $url = '';
        }
        return array($url, $canvas_id);
    }

}

?>
