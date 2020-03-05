<?php
/**
 * Class to provide admin and user-facing menus.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2020 Lee Garner <lee@leegarner.com>
 * @package     locator
 * @version     v1.2.1
 * @since       v1.2.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Locator;


/**
 * Class to provide admin and user-facing menus.
 * @package locator
 */
class Menu
{

    /**
     * Create the Administrative menu.
     *
     * @param   string  $view       Current view
     * @return  string      HTML for menu
     */
    public static function Admin($view='')
    {
        global $LANG_ADMIN, $LANG_GEO, $_CONF, $_CONF_GEO;

        USES_lib_admin();

        $retval = '';
        if (!empty($view) && isset($LANG_GEO['menu_hlp'][$view])) {
            $desc_text = $LANG_GEO['menu_hlp'][$view];
        }

        $menu_arr = array (
            array(
                'url' => LOCATOR_ADMIN_URL . '/index.php',
                'text' => $LANG_GEO['manage_locations'],
                'active' => $view == 'locations' ? true : false,
            ),
            array(
                'url' => LOCATOR_ADMIN_URL . '/index.php?mode=userloc',
                'text' => $LANG_GEO['manage_userlocs'],
                'active' => $view == 'userloc' ? true : false,
            ),
            array(
                'url' => $_CONF['site_admin_url'],
                'text' => $LANG_ADMIN['admin_home'],
            ),
        );

        $header_str = $LANG_GEO['plugin_name'] . ' ' . $LANG_GEO['version'] .
            ' ' . $_CONF_GEO['pi_version'];

        $retval .= ADMIN_createMenu($menu_arr, $view, '');
        return $retval;
    }


    /**
     * Show the site header, with or without left blocks according to config.
     *
     * @since   version 1.0.1
     * @see     COM_siteHeader()
     * @param   string  $subject    Text for page title (ad title, etc)
     * @param   string  $meta       Other meta info
     * @return  string              HTML for site header
     */
    public static function siteHeader($subject='', $meta='')
    {
        global $_CONF_GEO, $LANG_GEO;

        $retval = '';

        $title = $LANG_GEO['pi_title'];
        if ($subject != '') {
            $title = $subject . ' : ' . $title;
        }

        switch($_CONF_GEO['displayblocks']) {
        case 2:     // right only
        case 0:     // none
            $retval .= COM_siteHeader('none', $title, $meta);
            break;

        case 1:     // left only
        case 3:     // both
        default :
            $retval .= COM_siteHeader('menu', $title, $meta);
            break;
        }
        return $retval;
    }


    /**
     * Show the site footer, with or without right blocks according to config.
     *
     * @since  version 1.0.1
     * @see    COM_siteFooter()
     * @return string              HTML for site header
     */
    public static function siteFooter()
    {
        global $_CONF_GEO;

        $retval = '';

        switch($_CONF_GEO['displayblocks']) {
        case 2 : // right only
        case 3 : // left and right
            $retval .= COM_siteFooter(true);
            break;

        case 0: // none
        case 1: // left only
        default :
            $retval .= COM_siteFooter();
            break;
        }
        return $retval;
    }

}

?>
