<?php
/**
 * Base class for mappers. Mainly used to instantiate the configured mapper.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.1.4
 * @since       1.1.4
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Locator;

/**
 * Base class to return a Mapper.
 * @package locator
 */
class Mapper
{
    /** Indicate that this service provides mapping. Default = false.
     * @var boolean */
    protected $is_mapper = false;

    /** Indicate that this service provides Geocoding. Default = false.
     * @var boolean */
    protected $is_geocoder = false;

    /** Displaly name for this service.
     * @var string */
    protected $display_name = 'Undefined';

    /** Class name for this service.
     * @var string */
    protected $name = 'Undefined';

    /** Width of map, default = NULL.
     * @var string */
    protected $width = NULL;

    /** Height of map, defualt = NULL.
     * @var string */
    protected $height = NULL;

    /**
     * Get a service instance.
     *
     * @param   string  $name   Name of service
     * @return  object          Instance of service class.
     */
    public static function getInstance($name='')
    {
        global $_CONF_GEO;
        static $mappers = array();

        if ($name == '') $name = $_CONF_GEO['mapper'];
        if (!isset($mappers[$name])) {
            $clsname = '\\Locator\\Mappers\\' . $name;
            if (class_exists($clsname)) {
                $mappers[$name] = new $clsname();
            } else {
                $mappers[$name] = new self;
            }
        }
        return $mappers[$name];
    }


    /**
     * Get an instance of the configured mapping service.
     *
     * @return  object  Instance of a Mapper object.
     */
    public static function getMapper()
    {
        global $_CONF_GEO;

        return self::getInstance($_CONF_GEO['mapper']);
    }


    /**
     * Get an instance of the configured geocoding service.
     *
     * @return  object  Instance of a Mapper object.
     */
    public static function getGeocoder()
    {
        global $_CONF_GEO;

        return self::getInstance($_CONF_GEO['geocoder']);
    }


    /**
     * Get the display name of this service.
     *
     * @return  string      Service display name
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }


    /**
     * Get the internal classname of this service.
     *
     * @return  string      Class Name
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Default function to show a map, in case an invalid class was instantiated.
     *
     * @param   float   $lat    Latitude
     * @param   float   $lng    Longitude
     * @param   string  $text   Optional text for marker
     * @return  string          Empty string
     */
    public function showMap($lat, $lng, $text = '')
    {
        return '';
    }


    /**
     * Retrieve the contents of a remote URL.
     *
     * @param   string  $url    URL to retrieve
     * @return  string          Raw contents from URL
     */
    public static function getUrl($url)
    {
        if (in_array('curl', get_loaded_extensions())) {
            $agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; ' .
                    'rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR ' .
                    '3.5.30729)';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,            $url);
            curl_setopt($ch, CURLOPT_USERAGENT,      $agent);
            curl_setopt($ch, CURLOPT_HEADER,         0);
            curl_setopt($ch, CURLOPT_ENCODING,       "gzip");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($ch, CURLOPT_TIMEOUT,        8);

            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $result = '';
            COM_errorLog('LOCATOR: Missing url_fopen and curl support');
        }
        return $result;
    }


    /**
     * Get all providers into an array
     *
     * @return  array   Array of objects indexed by name
     */
    public static function getAll()
    {
        static $A = NULL;

        if ($A === NULL) {
            $files = glob(__DIR__ . '/Mappers/*.class.php');
            foreach ($files as $file) {
                $tmp = pathinfo($file, PATHINFO_FILENAME);
                $tmp = explode('.', $tmp);
                $cls = '\\Locator\\Mappers\\' . $tmp[0];
                $M = self::getInstance($tmp[0]);
                $A[$M->getName()] = $M;
            }
        }
        return $A;
    }


    /**
     * Get an array of all Geocoding providers
     *
     * @return  array   Array of objects indexed by name
     */
    public static function getGeocoders()
    {
        $mappers = self::getAll();
        $A = array();
        foreach ($mappers as $name=>$mapper) {
            if ($mapper->isGeocoder()) {
                $A[$name] = $mapper;
            }
        }
        return $A;
    }


    /**
     * Get an array of all Mapping providers.
     *
     * @return  array   Array of objects indexed by name
     */
    public static function getMappers()
    {
        $mappers = self::getAll();
        $A = array();
        foreach ($mappers as $name=>$mapper) {
            if ($mapper->isMapper()) {
                $A[$name] = $mapper;
            }
        }
        return $A;
    }


    /**
     * Check if this provider is a Mapping provider.
     *
     * @return  boolean     True or False
     */
    public function isMapper()
    {
        return $this->is_mapper;
    }


    /**
     * Check if this provider is a Geocoding provider.
     *
     * @return  boolean     True or False
     */
    public function isGeocoder()
    {
        return $this->is_geocoder;
    }


    /**
     * Get the form to show driving directions.
     * Google is the only mapper currently supported but if other mappers
     * can show directions then they can provide this function.
     *
     * @param   float   $lat    Destination Latitude
     * @param   float   $lng    Destination Logitude
     * @return  string          HTML for input form
     */
    public function showDirectionsForm($lat, $lng)
    {
        global $_CONF_GEO;

        if ($_CONF_GEO['use_directions']) {
            $T = new \Template(LOCATOR_PI_PATH . '/templates');
            $T->set_file('form', 'def_direction_form.thtml');
            $T->set_var(array(
                'lat'   => $lat,
                'lng'   => $lng,
            ) );
            $T->parse('output', 'form');
            return $T->finish($T->get_var('output'));
        } else {
            return '';
        }
    }


    /**
     * Set the map display width to override CSS.
     *
     * @param   string  $width  New width
     */
    public function setWidth($width)
    {
        // Change to pixel string if no unit is given.
        if (is_numeric($width)) {
            $width = "{$width}px";
        }
        $this->width = $width;
    }


    /**
     * Set the map display height to override CSS.
     *
     * @param   string  $height  New height
     */
    public function setHeight($height)
    {
        // Change to pixel string if no unit is given.
        if (is_numeric($height)) {
            $height = "{$height}px";
        }
        $this->height = $height;
    }


    /**
     * Get the CSS style string to apply to the outer map div element.
     *
     * @return  string  Style string, empty if no styles defined.
     */
    protected function getDivStyle()
    {
        $style = '';
        if ($this->width !== NULL) {
            $style .= 'width:' . $this->width . ';';
        }
        if ($this->height !== NULL) {
            $style .= 'height:' . $this->height . ';';
        }
        if ($style != '') {
            $style = 'style="' . $style . '"';
        }
        return $style;
    }

}   // class Mapper

?>
