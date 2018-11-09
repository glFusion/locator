<?php
/**
 * Sitemap driver for the Locator Plugin.
 * There are no categories in this plugin.
 *
 * @author      Lee P. Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2017 Lee P. Garner <lee@leegarner.com>
 * @package     locator
 * @version     1.1.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

/**
 * Sitemap class for the Locator plugin
 * @package locator
 */
class sitemap_locator extends sitemap_base
{
    /** Plugin name
     * @var string */
    protected $name = 'locator';


    /**
     * Get the display name when showing the sitemap.
     *
     * @return  string  Plugin display name
     */
    public function getDisplayName()
    {
        global $_CONF_GEO;
        return $_CONF_GEO['pi_display_name'];
    }


    /**
     * Get items for the sitemap.
     *
     * @param   mixed   $cat_id     Category ID (not used)
     * @return  array       Array of item information.
     */
    public function getItems($cat_id = false)
    {
        global $_TABLES, $_USER;

        $entries = array();
        $sql = "SELECT * FROM {$_TABLES['locator_markers']}"
                . COM_getPermSql('WHERE', $_USER['uid'], 2);
        $result = DB_query($sql, 1);
        if (DB_error()) {
            COM_errorLog("plugin_sitemap_locator() SQL error: $sql");
            return $entries;
        }
        while ($A = DB_fetchArray($result, false)) {
            $entries[] = array(
                'id'    => $A['id'],
                'title' => $A['title'],
                'uri'   => LOCATOR_URL . '/index.php?detail=x&amp;id='
                    . rawurlencode($A['id']),
                'date'  => $A['add_date'],
                'image_uri' => false,
            );
        }
        return $entries;
    }
}

?>
