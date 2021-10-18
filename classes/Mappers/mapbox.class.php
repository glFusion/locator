<?php
/**
 * Class for Mapbox services.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2020-2021 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     v1.2.2
 * @since       v1.2.2
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
class mapbox extends \Locator\Mapper
{
    /** The access token.
     * @var string */
    private $token = '';

    /** Indicate that this service provides mapping.
     * @var boolean */
    protected $is_mapper = true;

    /** Display name for this service.
     * @var string */
    protected $display_name = 'Mapbox';

    /** Class name for this service.
     * @var string */
    protected $name = 'mapbox';


    /**
     * Constructor. Nothing to do for this mapper.
     *
     * @param   string  $id     Optional ID of a location to load
     */
    public function __construct($id = '')
    {
        global $_CONF_GEO;
        if (isset($_CONF_GEO['mapbox_token'])) {
            $this->token = $_CONF_GEO['mapbox_token'];
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
        if (empty($this->token)) {
            COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . '():  API Key is required');
            return '';
        }

        $lat = (float)$lat;
        $lng = (float)$lng;
        if ($lat == 0 || $lng == 0) {
            return '';
        }

        $this->loadMapJS();
        $T = $this->getMapTemplate();
        $T->set_var(array(
            'lat'           => GEO_coord2str($lat, true),
            'lng'           => GEO_coord2str($lng, true),
            'canvas_id'     => rand(1,999),
            'api_key'       => $this->token,
            'text'          => str_replace('"', '&quot;', $text),
            'div_style'     => $this->getDivStyle(),
        ) );
        $T->parse('output','page');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Get the URL to an embeddable map image or iframe.
     * This is for a simplified URL which does not require the full javascript
     * initialization.
     *
     * @param   float   $lat    Latitude
     * @param   float   $lng    Longitude
     * @param   ?string $text   Optional text
     * @return  array       Array of type and url to embed
     */
    public function getStaticMap(float $lat, float $lng, ?string $text = '') : array
    {
        $url = "https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/pin-s+ec1313(" .
            "{$lng},{$lat})/{$lng},{$lat},15,0/400x400?access_token={$this->token}";
        return $this->_getStaticMap($url);
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
                'https://api.mapbox.com/mapbox.js/v3.3.1/mapbox.css',
                'text/css',
                HEADER_PRIO_NORMAL
            );
            $outputHandle->addLinkScript(
                'https://api.mapbox.com/mapbox.js/v3.3.1/mapbox.js'
            );
        }
        return $this;
    }

}
