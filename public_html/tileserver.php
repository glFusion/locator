<?php
/**
 * Tile server for the Locator plugin.
 * Delivers an openstreetmap.org map tile from cache, if available,
 * or downloads from openstreetmap.org and saves in cache.
 * For performance there is no integration with glFusion.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018-2020 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.2.1
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 * @filesource
 */


/**
 * Get a tile image from cache.
 *
 * @param   string  $cache_key  Cache Key
 * @return  string|null Image contents, NULL if not found
 */
function LOC_tileserver_getcache($cache_key)
{
    $filepath = __DIR__ . '/tilecache/' . $cache_key[0] . '/' . $cache_key . '.png';
    if (is_file($filepath)) {
        $img = file_get_contents($filepath);
    } else {
        $img = NULL;
    }
    return $img;
}


/**
 * Write an image into the cache.
 *
 * @param   string  $cache_key  Cache Key
 * @param   binary  $img        Image data
 */
function LOC_tileserver_writecache($cache_key, $img)
{
    $filepath = __DIR__ . '/tilecache/' . $cache_key[0];
    if (!is_dir($filepath)) {
        mkdir($filepath, 0755);
    }
    $filepath .= '/' . $cache_key . '.png';
    file_put_contents($filepath, $img);
}


$args = array('z', 'x', 'y');
foreach ($args as $argname) {
    if (isset($_GET[$argname])) {
        $$argname = $_GET[$argname];
    }
}
if (!isset($x) || !isset($y) || !isset($z)) {
    exit;
}

$url = "https://a.tile.openstreetmap.org/$z/$x/$y.png";
//$url = 'http://tile.thunderforest.com/cycle/{z}/{x}/{y}.png';
//$url = 'http://a.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png';
/*$url = str_replace(
    array('{z}', '{x}', '{y}'),
    array($z, $x, $y),
    $url
);*/
/* Uncomment to debug
$fp = fopen('/tmp/tileserver.log', 'a+');
fputs($fp, $url . "\n");
fclose($fp);
 */

//$cache_key = 'tiles_' . md5($url);
$cache_key = md5($url);
$img = LOC_tileserver_getcache($cache_key);
if ($img === NULL) {
    $img = '';
    if (in_array('curl', get_loaded_extensions())) {
        $agent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; ' .
                    'rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR ' .
                    '3.5.30729)';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_USERAGENT,      $agent);
        curl_setopt($ch, CURLOPT_HEADER,         false);
        curl_setopt($ch, CURLOPT_ENCODING,       "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT,        8);

        $img = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ((int)$httpcode == 200) {
           LOC_tileserver_writecache($cache_key, $img);
        } else {
            $img = '';
        }
    }
}

header('Content-Type: ' . 'image/png');
header('Accept-Ranges: none'); //Changed this because we don't accept range requests
//header('Last-Modified: ' . $gmdate_modified);
header('Content-Length: ' . strlen($img));
header('cache-control: max-age=19730');
echo $img;

exit;

?>
