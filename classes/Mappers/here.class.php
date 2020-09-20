<?php
/**
 * Class for here.com services.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2020 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.3.0
 * @since       1.3.0
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Locator\Mappers;
use Locator\Cache;


/**
 * Provides geocoding service from the U.S. Census. Does not provide mapping.
 * @since   version 1.2.0
 * @package locator
 */
class here extends \Locator\Mapper
{
    /** The REST API key.
     * @var string */
    private $rest_api_key = '';

    /** The Javascript key.
     * @var string */
    private $js_key = '';

    /** Indicate that this service provides mapping.
     * @var boolean */
    protected $is_mapper = true;

    /** Indicate that this service provides geocoding.
     * @var boolean */
    protected $is_geocoder = true;

    /** Display name for this service.
     * @var string */
    protected $display_name = 'Here.com';

    /** Class name for this service.
     * @var string */
    protected $name = 'here';

    /** Geocoding URL for this service.
     * @const string */
    const GEOCODE_URL = 'https://geocoder.ls.hereapi.com/search/6.2/geocode.json?languages=en-US&maxresults=4&searchtext=%s&apiKey=%s';


    /**
     * Constructor. Nothing to do for this mapper.
     *
     * @param   string  $id     Optional ID of a location to load
     */
    public function __construct($id = '')
    {
        global $_CONF_GEO;
        if (isset($_CONF_GEO['here_rest_key'])) {
            $this->rest_api_key = $_CONF_GEO['here_rest_key'];
        }
        if (isset($_CONF_GEO['here_js_key'])) {
            $this->js_key = $_CONF_GEO['here_js_key'];
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
        $data = Cache::get($cache_key);
        if ($data === NULL) {
            $url = sprintf(self::GEOCODE_URL, urlencode($address), $this->rest_api_key);
            $json = self::getUrl($url);
            $data = json_decode($json, true);
            if (
                !isset($data['Response']['View'][0]['Result']) ||
                empty($data['Response']['View'][0]['Result'])
            ) {
                return -1;
            }

            Cache::set($cache_key, $data);
        }
        $loc = $data['Response']['View'][0]['Result'][0]['Location']['DisplayPosition'];

        if (!isset($loc['Longitude']) || !isset($loc['Latitude'])) {
            $lat = 0;
            $lng = 0;
            return -1;
        } else {
            $lat = $loc['Latitude'];
            $lng = $loc['Longitude'];
            return 0;
        }
    }


    /**
     * Display a map.
     *
     * @param   float   $lat    Latitude
     * @param   float   $lng    Longitude
     * @param   string  $text   Optional text for marker
     * @return  string          HTML to generate the map
     */
    public function showMap($lat, $lng, $text = '')
    {
        global $_CONF_GEO, $_CONF;

        // Insert a map, if configured correctly
        if ($_CONF_GEO['show_map'] == 0) {
            return '';
        }
        if (empty($this->js_key)) {
            COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . '():  API Key is required');
            return '';
        }

        $lat = (float)$lat;
        $lng = (float)$lng;
        if ($lat == 0 || $lng == 0) {
            return '';
        }

        $this->loadMapJS();
        $T = new \Template(LOCATOR_PI_PATH . '/templates/' . $this->getName());
        $T->set_file('page', 'map.thtml');
        $T->set_var(array(
            'lat'           => GEO_coord2str($lat, true),
            'lng'           => GEO_coord2str($lng, true),
            'canvas_id'     => rand(1,999),
            'api_key'       => $this->js_key,
            'text'          => str_replace('"', '&quot;', $text),
            'div_style'     => $this->getDivStyle(),
        ) );
        $T->parse('output','page');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Get the URL to map JS and CSS for inclusion in a template.
     * This makes sure the javascript is included only once even if there
     * are multiple maps on the page.
     * Returns the URL, and a random number to be used for the canvas ID.
     *
     * @return  array   $url=>URL to javascript, $canvas_id=> random ID
     */
    private function loadMapJS()
    {
        static $have_map_js = false;    // Flag to avoid duplicate loading

        if (!$have_map_js) {
            $have_map_js = true;
            $outputHandle = \outputHandler::getInstance();
            $outputHandle->addLink(
                'stylesheet',
                'https://js.api.here.com/v3/3.1/mapsjs-ui.css',
                'text/css',
                HEADER_PRIO_NORMAL
            );
            $outputHandle->addLinkScript(
                'https://js.api.here.com/v3/3.1/mapsjs-core.js'
            );
            $outputHandle->addLinkScript(
                'https://js.api.here.com/v3/3.1/mapsjs-service.js'
            );
            $outputHandle->addLinkScript(
                'https://js.api.here.com/v3/3.1/mapsjs-ui.js'
            );
            $outputHandle->addLinkScript(
                'https://js.api.here.com/v3/3.1/mapsjs-mapevents.js'
            );
        }
        return $this;
    }

}

?>
