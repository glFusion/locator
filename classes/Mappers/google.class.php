<?php
/**
 * Class for Google Maps.
 * Supports geocoding and mapping.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018-2021 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     v1.2.2
 * @since       v1.1.4
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Locator\Mappers;

/**
 * Class to handle the general location markers.
 * @package locator
 */
class google extends \Locator\Mapper
{
    /** ISO language identifier.
     * @var string */
    private $_lang;

    /** API Key for Mapping Javascript API.
     * @var string */
    private $js_key = NULL;

    /** API Key for Geocoding API.
     * @var string */
    private $geocode_key = NULL;

    /** Indicate that this service provides mapping.
     * @var boolean */
    protected $is_mapper = true;

    /** Indicate that this service provides geocoding.
     * @var boolean */
    protected $is_geocoder = true;

    /** Display name for this service.
     * @var string */
    protected $display_name = 'Google';

    /** Internal name for this service.
     * @var string */
    protected $name = 'google';

    /** URL to maps javascript. */
    const GEO_MAP_URL = 'https://maps.google.com/maps/api/js?key=%s&callback=initializeGMap';

    /** Geocoding url, address will be appended to this. */
    const GEO_GOOG_URL = 'https://maps.googleapis.com/maps/api/geocode/json?address=';

    /**
     * Constructor
     *
     * @param   string  $id     Optional ID of a location to load
     */
    public function __construct($id = '')
    {
        global $_CONF_GEO;

        // Load supported languages
        $this->_lang = array(
            'ar',
            'bg', 'bn',
            'ca', 'cs',
            'da', 'de',
            'el', 'en', 'en-AU', 'en-GB', 'es', 'eu',
            'fa', 'fi', 'fil', 'fr',
            'gl', 'gu', 'hi', 'hr', 'hu',
            'id', 'it', 'iw',
            'ja',
            'kn', 'ko',
            'lt', 'lv',
            'ml', 'mr',
            'nl', 'no',
            'pl', 'pt', 'pt-BR', 'pt-PT',
            'ro', 'ru',
            'sk', 'sl', 'sr', 'sv',
            'tl', 'ta', 'te', 'th', 'tr',
            'uk',
            'vi',
            'zh-CN', 'zh-TW',
        );

        $this->geocode_key = $_CONF_GEO['google_api_key'];
        if (!empty($_CONF_GEO['google_js_key'])) {
            $this->js_key = $_CONF_GEO['google_js_key'];
        } else {
            $this->js_key = $this->geocode_key;
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

        // Insert a google map, if configured correctly
        if ($_CONF_GEO['show_map'] == 0) {
            return '';
        }

        $lat = (float)$lat;
        $lng = (float)$lng;
        if ($lat == 0 || $lng == 0)
        return '';

        // Check that the site language is supported by Google,
        // default to English if not.
        $iso_lang = in_array($_CONF['iso_lang'], $this->_lang) ?
                $_CONF['iso_lang'] : 'en';

        $this->loadMapJS();
        $T = new \Template(LOCATOR_PI_PATH . '/templates/' . $this->getName());
        $T->set_file('page', 'map.thtml');
        $T->set_var(array(
            'lat'           => GEO_coord2str($lat, true),
            'lng'           => GEO_coord2str($lng, true),
            'infowindow_text' => str_replace('"', '&quot;', COM_checkHTML($text)),
            'iso_lang'      => $iso_lang,
            'canvas_id'     => rand(1,999),
            'div_style'     => $this->getDivStyle(),
        ) );
        $T->parse('output','page');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Get the URL to a map image.
     * This is for a simplified URL which does not require the full javascript
     * initialization.
     *
     * @param   float   $lat    Latitude
     * @param   float   $lng    Longitude
     * @param   ?string $text   Optional text
     * @return  array       Array of type and url to embed
     */
    public function getEmbeddedMap(float $lat, float $lng, ?string $text = '') : array
    {
        $url = "https://maps.google.com/maps?q={$lat},{$lng}&t=&z=13&ie=UTF8&iwloc=&output=embed";
        return array(
            'type' => 'iframe',
            'url' => $url,
        );
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
        global $_CONF_GEO;

        if ($_CONF_GEO['autofill_coord'] != 1 || empty($address))
            return 0;

        $lat = 0;
        $lng = 0;
        $cache_key = $this->getName() . '_geocode_' . md5($address);
        $data = \Locator\Cache::get($cache_key);
        if ($data === NULL) {
            $address = urlencode(GEO_AddressToString($address));
            if (empty($this->geocode_key)) {
                COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . '():  API Key is required');
                return -1;
            }
            $url = self::GEO_GOOG_URL . $address . '&key=' . $this->geocode_key;
            $json = self::getUrl($url);
            if ($json == '') {
                return 0;
            }
            $data = json_decode($json, true);
            if (!is_array($data)) {
                return -1;
            } elseif ($data['status'] != 'OK') {
                COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . '(): ' . $data['status'] . ' - ' . $data['error_message']);
                return -1;
            }
            \Locator\Cache::set($cache_key, $data);
        }
        if (isset($data['results'][0]['geometry']['location']) && is_array($data['results'][0]['geometry']['location'])) {
            $lat = $data['results'][0]['geometry']['location']['lat'];
            $lng = $data['results'][0]['geometry']['location']['lng'];
        }
        return 0;
    }


    /**
     * Get the URL to Google Maps for inclusion in a template.
     * This makes sure the javascript is included only once even if there
     * are multiple maps on the page.
     * Returns the URL, and a random number to be used for the canvas ID.
     *
     * @return  array   $url=>URL to Google Maps javascript, $canvas_id=> random ID
     */
    private function loadMapJS()
    {
        global $_CONF_GEO;
        static $have_map_js = false;    // Flag to avoid duplicate loading

        $url = '';
        if (!$have_map_js) {
            $have_map_js = true;
            if (!empty($this->js_key)) {
                $outputHandle = \outputHandler::getInstance();
                $outputHandle->addLink(
                    'stylesheet',
                    'https://api.mqcdn.com/sdk/mapquest-js/v1.3.2/mapquest.css',
                    'text/css',
                    HEADER_PRIO_NORMAL
                );
                $outputHandle->addLinkScript(
                    sprintf(self::GEO_MAP_URL, $this->js_key)
                );
            } else {
                COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . '():  API Key is required');
            }
        }
        return $this;
    }

}
