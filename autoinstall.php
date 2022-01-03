<?php
/**
 * Automatic plugin installation for the QR Code plugin.
 * Based on the Geeklog plugin by Yoshinori Tahara
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @author      Yoshinori Tahara <taharaxp@gmail.com>
 * @copyright   Copyright (c) 2010-2016 Lee Garner <lee@leegarner.com>
 * @copyright   2010 Yoshinori Tahara - dengen - taharaxp AT gmail DOT com
 * @package     qrcode
 * @version     v1.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

require_once __DIR__ . '/qrcode.php';

global $INSTALL_plugin, $_QRC_CONF;
$INSTALL_plugin[$_QRC_CONF['pi_name']] = array(
    'installer' => array(
        'type' => 'installer',
        'version' => '1',
        'mode' => 'install',
    ),

   'plugin' => array(
       'type'      => 'plugin',
       'name'      => $_QRC_CONF['pi_name'],
       'display'   => $_QRC_CONF['pi_display_name'],
       'ver'       => $_QRC_CONF['pi_version'],
       'gl_ver'    => $_QRC_CONF['gl_version'],
       'url'       => $_QRC_CONF['pi_url'],
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
        'dirs' => array($_QRC_CONF['img_path']),
    ),
);


/**
 * Plugin autoinstall function.
 *
 * @param   string  $pi_name    Plugin name
 * @return  array               Plugin information
 */
function plugin_install_qrcode()
{
    global $_QRC_CONF, $_CONF, $INSTALL_plugin;

    COM_errorLog("Attempting to install the {$_QRC_CONF['pi_display_name']} plugin", 1);

    USES_lib_install();
    $ret = INSTALLER_install($INSTALL_plugin[$_QRC_CONF['pi_name']]);
    return $ret == 0 ? true : false;
}


/**
 * Load plugin configuration from database.
 *
 * @return  boolean             true on success, otherwise false
 * @see     plugin_initconfig_qrcode
 */
function plugin_load_configuration_qrcode()
{
    global $_CONF;

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once dirname(__FILE__) . '/install_defaults.php';

    return plugin_initconfig_qrcode();
}


/**
 * Automatic uninstall function for plugins.
 * This code is automatically uninstalling the plugin.
 * It passes an array to the core code function that removes
 * tables, groups, features and php blocks from the tables.
 * Additionally, this code can perform special actions that cannot be
 * foreseen by the core code (interactions with other plugins for example)
 *
 * @return  array
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

?>
