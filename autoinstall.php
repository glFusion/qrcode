<?php
/**
*   Automatic plugin installation for the QR Code plugin
*   Based on the Geeklog plugin by Yoshinori Tahara
*
*   @author     Lee Garner <lee@leegarner.com>
*   @author     Yoshinori Tahara <taharaxp@gmail.com>
*   @copyright  Copyright (c) 2010-2014 Lee Garner <lee@leegarner.com>
*   @copyright  2010 Yoshinori Tahara - dengen - taharaxp AT gmail DOT com
*   @package    qrcode
*   @version    1.0.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

require_once $_CONF['path'].'plugins/qrcode/qrcode.php';

/**
*   Plugin autoinstall function
*
*   @param    string  $pi_name    Plugin name
*   @return   array               Plugin information
*/
function plugin_install_qrcode()
{
    global $_QRC_CONF, $_CONF;

    $inst_params = array(
        'installer' => array(
            'type' => 'installer', 
            'version' => '1', 
            'mode' => 'install'),

        'plugin' => array(
            'type' => 'plugin',
            'name' => $_QRC_CONF['pi_name'],
            'display' => $_QRC_CONF['pi_display_name'],
            'ver' => $_QRC_CONF['pi_varsion'],
            'gl_ver' => $_QRC_CONF['gl_version'],
        ),

        array('type' => 'group', 
            'group' => $_QRC_CONF['pi_name'] .' Admin', 
            'desc' => 'Users in this group can administer the ' .
                        $_QRC_CONF['pi_display_name'] . ' plugin',
            'variable' => 'admin_group_id', 
            'admin' => true,
            'addroot' => true,
        ),

        array('type' => 'feature', 
            'feature' => $_QRC_CONF['pi_name'] . '.admin', 
            'desc' => 'Can administer the ' . $_QRC_CONF['pi_display_name'] . ' plugin',
            'variable' => 'admin_feature_id',
        ),

        array('type' => 'mapping', 
            'group' => 'admin_group_id', 
            'feature' => 'admin_feature_id',
            'log' => 'Adding admin feature to the admin group',
        ),

        array(
            'type'  => 'mkdir',
            'dirs' => array($_CONF['path'] . 'data/' . $_QRC_CONF['pi_name']),
        ),

    );

    COM_errorLog("Attempting to install the {$_QRC_CONF['pi_display_name']} plugin", 1);

    USES_lib_install();
    $ret = INSTALLER_install($inst_params);
    if ($ret > 0) {
        return false;
    } else {
        return true;
    }
}


/**
*   Load plugin configuration from database
*
*   @return   boolean             true on success, otherwise false
*   @see      plugin_initconfig_qrcode
*/
function plugin_load_configuration_qrcode()
{
    global $_CONF, $_QRC_CONF;

    $base_path = $_CONF['path'] . 'plugins/' . $_QRC_CONF['pi_name'] . '/';

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
        'groups' => array('qrcode Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('qrcode.admin'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars' => array('qrcode_gid'),
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
