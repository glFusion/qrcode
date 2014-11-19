<?php
//  $Id: install.php 70 2010-05-03 16:44:09Z root $
/**
*   Manual installation for the QRcode plugin
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2011 Lee Garner <lee@leegarner.com>
*   @package    qrcode
*   @version    1.0.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

/** Include required common glFusion functions */
require_once('../../../lib-common.php');

// Only let Root users access this page
if (!SEC_inGroup('Root')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the qrcode install/uninstall page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    $display = COM_siteHeader();
    $display .= COM_startBlock($LANG_GEO['access_denied']);
    $display .= $LANG_GEO['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

$base_path = "{$_CONF['path']}plugins/qrcode";
require_once $base_path . '/functions.inc';

// Default data
$DEFVALUES = array();

// Security Feature to add
$NEWFEATURE = array();
$NEWFEATURE['qrcode.admin']  ="qrcode Admin Rights";


/**
*   Puts the datastructures for this plugin into the Geeklog database
*   Note: Corresponding uninstall routine is in functions.inc
*   @return   boolean True if successful False otherwise
*   @ignore
*/
function plugin_install_qrcode()
{
    global $NEWFEATURE, $_QRC_CONF, $_CONF, $_TABLES;

    $pi_name = $_QRC_CONF['pi_name'];
    $pi_version = $_QRC_CONF['pi_version'];
    $pi_url = $_QRC_CONF['pi_url'];
    $gl_version = $_QRC_CONF['gl_version'];

    COM_errorLog("Attempting to install the $pi_name Plugin",1);

    // Create the plugin admin security group
    COM_errorLog("Attempting to create $pi_name admin group", 1);
    DB_query("INSERT INTO 
            {$_TABLES['groups']} 
            (grp_name, grp_descr) 
        VALUES 
            ('$pi_name Admin', 
            'Users in this group can administer the $pi_name plugin')",
    1);
    if (DB_error()) {
        plugin_autouninstall_qrcode();
        return false;
        exit;
    }
    COM_errorLog('...success',1);
    $group_id = DB_insertId();

    // Save the grp id for later uninstall
    COM_errorLog('About to save group_id to vars table for use during uninstall',1);
    DB_query("INSERT INTO 
            {$_TABLES['vars']} 
        VALUES 
            ('{$pi_name}_gid', $group_id)",
    1);
    if (DB_error()) {
        plugin_autouninstall_qrcode();
        return false;
        exit;
    }
    COM_errorLog('...success',1);

    // Add plugin Features
    foreach ($NEWFEATURE as $feature => $desc) {
        COM_errorLog("Adding $feature feature",1);
        DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr) "
            . "VALUES ('$feature','$desc')",1);
        if (DB_error()) {
            COM_errorLog("Failure adding $feature feature",1);
            plugin_autouninstall_qrcode();
            return false;
            exit;
        }
        $feat_id = DB_insertId();
        COM_errorLog("Success",1);
        COM_errorLog("Adding $feature feature to admin group",1);
        DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)");
        if (DB_error()) {
            COM_errorLog("Failure adding $feature feature to admin group",1);
            plugin_autouninstall_qrcode();
            return false;
            exit;
        }
        COM_errorLog("Success",1);
    }        

    // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
    COM_errorLog("Attempting to give all users in Root group access to $pi_name admin group",1);
    DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($group_id, NULL, 1)");
    if (DB_error()) {
        plugin_autouninstall_qrcode();
        return false;
        exit;
    }

    // Load the online configuration records
    if (function_exists('plugin_load_configuration')) {
        if (!plugin_load_configuration($group_id)) {
            PLG_uninstall($pi_name);
            return false;
        }
    }

    // Register the plugin with glFusion
    COM_errorLog("Registering $pi_name plugin with glFusion", 1);
    DB_delete($_TABLES['plugins'],'pi_name',$pi_name);
    DB_query("INSERT INTO 
            {$_TABLES['plugins']} 
            (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) 
        VALUES 
            ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)"
    );
    if (DB_error()) {
        plugin_autouninstall_qrcode();
        return false;
        exit;
    }

    COM_errorLog("Succesfully installed the $pi_name Plugin!",1);
    return true;
}


/**
*   Load configuration items into the config system
*   @param  integer $group_id   Group ID to use as default (optional)
*   @return boolean             Result from plugin_initconfig_qrcode()
*/
function plugin_load_configuration($group_id=0)
{
    global $_CONF, $base_path;

    /** Include the glFusion configuration class */
    require_once $_CONF['path_system'] . 'classes/config.class.php';

    /** Include the default configuration values */
    require_once $base_path . '/install_defaults.php';

    return plugin_initconfig_qrcode($group_id);
}


/* 
* Main Function
*/

$action = $_REQUEST['action'];
switch ($action) {
case 'install':
    if (plugin_install_qrcode()) {
        echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=44');
    } else {
        echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=72');
    }
    exit;
    break;

case 'uninstall':
   if (plugin_autouninstall_qrcode('installed')) {
        echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=45');
    } else {
        echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php?msg=73');
    }
    break;
}

echo COM_refresh($_CONF['site_admin_url'] . '/plugins.php');

?>
