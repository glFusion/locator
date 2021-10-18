<?php
/**
 * Class for OpenStreetMap.org provider
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018-2021 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     v1.2.2
 * @since       v1.2.0
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Locator\Mappers;


/**
 * Provide openstreetmap.org mapping and geocoding services.
 * @package locator
 */
class openstreetmap extends \Locator\Mapper
{
    /** Indicate that this service provides mapping.
     * @var boolean */
    protected $is_mapper = true;

    /** Indicate that this service provides geocoding.
     * @var boolean */
    protected $is_geocoder = true;

    /** Display name for this provider.
     * @var string */
    protected $display_name = 'OpenStreetMap';

    /** Internal name for this provider.
     * @var string */
    protected $name = 'openstreetmap';

    /** Tile server URL.
     * @var string */
    protected $tileserver = 'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png';

    /** URL to geocoding service.
     * @const string */
    const GEOCODE_URL = 'https://nominatim.openstreetmap.org/search?format=json&q=%s';


    /**
     * Set the tile server if used.
     *
     * @param   string  $id     Optional ID of a location to load
     */
    public function __construct($id = '')
    {
        global $_CONF_GEO;

        if ($_CONF_GEO['osm_use_tileserver']) {
            $this->tileserver = LOCATOR_URL . '/tileserver.php?z={z}&x={x}&y={y}';
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
            'text'          => str_replace('"', '&quot;', $text),
            'div_style'     => $this->getDivStyle(),
            'tileserver_url' => $this->tileserver,
        ) );
        // OSM requires some URL params like {x} in the template.
        // Make sure they're kept and not assumed to be template vars.
        $T->set_unknowns('keep');
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
        $url = sprintf(self::GEOCODE_URL, urlencode($address));
        $json = $this->getUrl($url);
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data[0]['place_id'])) {
            COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . '(): Decoding Error - ' . $json);
            return -1;
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
    private function loadMapJS()
    {
        static $have_map_js = false;    // Flag to avoid duplicate loading

        $canvas_id = rand(1,999);   // Create a random id for the canvas
        if (!$have_map_js) {
            $have_map_js = true;
            $outputHandle = \outputHandler::getInstance();
            $outputHandle->addRaw(
                '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" type="text/css">'
            );
            $outputHandle->addRaw(
                '<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>'
            );
        }
        return $this;
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
    public function getStaticMap(float $lat, float $lng, ?string $text = '') : array
    {
        $url = "https://www.openstreetmap.org/export/embed.html?bbox={$lng}%2C{$lat}%2C{$lng}%2C{$lat}&amp;layer=mapnik&amp;marker={$lat}%2C{$lng}";
        return array(
            'type' => 'iframe',
            'url' => $url,
        );
    }

}

