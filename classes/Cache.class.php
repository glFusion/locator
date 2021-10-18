<?php
/**
 * Class to cache DB and web lookup results.
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
namespace Locator;

/**
 * Class for Locator Cache.
 * If glFusion is version 2.0.0 or higher then the phpFastCache is used,
 * Otherwise a database cache table is used.
 * @package locator
 */
class Cache
{
    /** Default tag added to all cache entries */
    const TAG = 'locator';

    /** Minimum glFusion version that supports caching */
    const MIN_GVERSION = '2.0.1';

    /**
     * Update the cache.
     * Adds an array of tags including the plugin name
     *
     * @param   string  $key    Item key
     * @param   mixed   $data   Data, typically an array
     * @param   mixed   $tag    Tag, or array of tags.
     * @param   integer $cache_mins Cache minutes
     * @return  boolean     True on success, False on error
     */
    public static function set(string $key, string $data, ?string $tag='', ?int $cache_mins=1440)
    {
        global $_TABLES;

        $key = self::makeKey($key);
        $ttl = (int)$cache_mins * 60;   // convert to seconds
        if (version_compare(GVERSION, self::MIN_GVERSION, '<')) {
            $ttl += time();
            $data = DB_escapeString($data);
            $sql = "INSERT INTO {$_TABLES['locator_cache']} VALUES ('$key', $ttl, '$data')
                ON DUPLICATE KEY UPDATE expires = $ttl, data = '$data'";
            DB_query($sql, 1);
        } else {
            // Always make sure the base tag is included
            $tags = array(self::TAG);
            if (!empty($tag)) {
                if (!is_array($tag)) $tag = array($tag);
                $tags = array_merge($tags, $tag);
            }
            return \glFusion\Cache\Cache::getInstance()->set($key, $data, $tags, $ttl);
        }
    }


    /**
     * Delete a single item from the cache by key.
     *
     * @param   string  $key    Base key, e.g. item ID
     * @return  boolean     True on success, False on error
     */
    public static function delete($key)
    {
        global $_TABLES;

        $key = self::makeKey($key);
        if (version_compare(GVERSION, self::MIN_GVERSION, '<')) {
            DB_delete($_TABLES['locator_cache'], 'cache_id', $key);
            return DB_error() ? false : true;
        } else {
            return \glFusion\Cache\Cache::getInstance()->delete($key);
        }
    }


    /**
     * Completely clear the cache. Called after upgrade.
     *
     * @param   array   $tag    Optional array of tags, base tag used if undefined
     * @return  boolean     True on success, False on error
     */
    public static function clear($tag = array())
    {
        global $_TABLES;

        // If clearing everything, also purge old static map images.
        if (empty($tag)) {
            self::_cleanImageCache();
        }

        if (version_compare(GVERSION, self::MIN_GVERSION, '<')) {
            DB_query("TRUNCATE {$_TABLES['locator_cache']}");
            return DB_error() ? false : true;
        } else {
            $tags = array(self::TAG);
            if (!empty($tag)) {
                if (!is_array($tag)) $tag = array($tag);
                $tags = array_merge($tags, $tag);
            }
            return \glFusion\Cache\Cache::getInstance()->deleteItemsByTagsAll($tags);
        }
    }


    /**
     * Create a unique cache key.
     * Intended for internal use, but public in case it is needed.
     *
     * @param   string  $key    Base key, e.g. Item ID
     * @param   boolean $incl_sechash   True to include the security hash
     * @return  string          Encoded key string to use as a cache ID
     */
    public static function makeKey($key, $incl_sechash = false)
    {
        if (version_compare(GVERSION, self::MIN_GVERSION, '>=')) {
            if ($incl_sechash) {
                // Call the parent class function to use the security hash
                $key = \glFusion\Cache\Cache::getInstance()->createKey(self::TAG . '_' . $key);
            } else {
                // Just generate a simple string key
                $key = self::TAG . '_' . $key;
            }
        }
        return substr($key, 0, 127);    // Make sure it fits the DB key field
    }


    /**
     * Get an item from cache by key.
     *
     * @param   string  $key    Key to retrieve
     * @return  mixed       Value of key, or NULL if not found
     */
    public static function get($key)
    {
        global $_TABLES;

        $key = self::makeKey($key);
        if (version_compare(GVERSION, self::MIN_GVERSION, '<')) {
            $data = DB_getItem(
                $_TABLES['locator_cache'],
                'data',
                "cache_id = '$key' AND expires > UNIX_TIMESTAMP()"
            );
            return empty($data) ? $data : NULL;
        } else {
            if (\glFusion\Cache\Cache::getInstance()->has($key)) {
                return \glFusion\Cache\Cache::getInstance()->get($key);
            } else {
                return NULL;
            }
        }
    }


    /**
     * Clear old cached static map images.
     */
    private static function _cleanImageCache() : void
    {
        // TODO: config vars
        $cache_clean_interval = 900;
        $cache_max_age = 1440;

        $cachedir = Mapper::getImageCacheDir();
        $lastCleanFile = $cachedir . '/lastclean.touch';

        //If this is a new timthumb installation we need to create the file
        if (!is_file($lastCleanFile)) {
            @touch($lastCleanFile));
        }

        if (@filemtime($lastCleanFile) < (time() - $cache_clean_interval)) {
            touch($lastCleanFile);
            $files = glob($cachedir . '*');
            if ($files) {
                $timeAgo = time() - $cache_max_age;
                foreach ($files as $file) {
                    if (@filemtime($file) < $timeAgo) {
                        @unlink($file);
                    }
                }
            }
            return true;
        }
    }

}
