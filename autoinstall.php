<?php
//  $Id$
/**
*   Automatic plugin installation for the QR Code plugin
*   Based on the Geeklog plugin by Yoshinori Tahara
*
*   @author     Lee Garner <lee@leegarner.com>
*   @author     Yoshinori Tahara <taharaxp@gmail.com>
*   @copyright  Copyright (c) 2010-2014 Lee Garner <lee@leegarner.com>
*   @copyright  2010 Yoshinori Tahara - dengen - taharaxp AT gmail DOT com
*   @package    qrcode
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

/**
*   Plugin autoinstall function
*
*   @param    string  $pi_name    Plugin name
*   @return   array               Plugin information
*/
function plugin_autoinstall_qrcode($pi_name)
{
    $pi_name         = 'qrcode';
    $pi_display_name = 'QRcode';
    $pi_admin        = $pi_display_name . ' Admin';

    $inst_parms = array(
        'info' => array(
            'pi_name'         => $pi_name,
            'pi_display_name' => $pi_display_name,
            'pi_version'      => '1.0.0',
            'pi_gl_version'   => '1.2.0',
            'pi_homepage'     => 'http://www.trybase.com/~dengen/log/',
        ),
        'groups' => array(
            $pi_admin => 'Users in this group can administer the '
                     . $pi_display_name . ' plugin'
        ),
        'features' => array(
            $pi_name . '.admin'    => 'Full access to ' . $pi_display_name . ' plugin'
        ),
        'mappings' => array(
            $pi_name . '.admin'    => array($pi_admin)
        ),
        'tables' => array(
        ),
    );

    return $inst_parms;
}


/**
*   Load plugin configuration from database
*
*   @param    string  $pi_name    Plugin name
*   @return   boolean             true on success, otherwise false
*   @see      plugin_initconfig_qrcode
*/
function plugin_load_configuration_qrcode($pi_name)
{
    global $_CONF;

    $base_path = $_CONF['path'] . 'plugins/' . $pi_name . '/';

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $base_path . 'install_defaults.php';

    return plugin_initconfig_qrcode();
}


/**
*   Check if the plugin is compatible with this Geeklog version
*
*   @param    string  $pi_name    Plugin name
*   @return   boolean             true: plugin compatible; false: not compatible
*/
function plugin_compatible_with_this_version_qrcode($pi_name)
{
    if (!function_exists('COM_truncate') || !function_exists('MBYTE_strpos')) {
        return false;
    }

    if (!function_exists('SEC_createToken')) {
        return false;
    }

    if (!function_exists('COM_showMessageText')) {
        return false;
    }

    if (!function_exists('SEC_getTokenExpiryNotice')) {
        return false;
    }

    if (!function_exists('SEC_loginRequiredForm')) {
        return false;
    }

    return true;
}


/**
*   Automatic uninstall function for plugins
*
*   @return   array
*
*   This code is automatically uninstalling the plugin.
*   It passes an array to the core code function that removes
*   tables, groups, features and php blocks from the tables.
*   Additionally, this code can perform special actions that cannot be
*   foreseen by the core code (interactions with other plugins for example)
*/
function QRC_autouninstall()
{
    return array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array(),
        /* give the full name of the group, as in the db */
        'groups' => array('QRcode Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('qrcode.admin'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars' => array()
    );
}


/**
*   Called by the plugin Editor to run the SQL Update for a plugin update
*/
function QRC_upgrade()
{
    global $_TABLES;

    $pi_name = 'qrcode';
    $installed_version = DB_getItem($_TABLES['plugins'], 'pi_version', "pi_name = '$pi_name'");

    $function = 'plugin_chkVersion_' . $pi_name;
    $code_version = $function();
    if ($installed_version == $code_version) return true;

    $function = 'plugin_compatible_with_this_version_' . $pi_name;
    if (!$function($pi_name)) return 3002;

    $function = 'plugin_autoinstall_' . $pi_name;
    $inst_parms = $function($pi_name);
    $pi_gl_version = $inst_parms['info']['pi_gl_version'];

    // update the version numbers
    DB_query("UPDATE {$_TABLES['plugins']} "
           . "SET pi_version = '$code_version', pi_gl_version = '$pi_gl_version' "
           . "WHERE pi_name = '$pi_name'");

    COM_errorLog(ucfirst($pi_name)
        . " plugin was successfully updated to version $code_version.");

    return true;
}

?>
