<?php
/**
 * Perform upgrade functions for QRCode.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2016-2022 Lee Garner <lee@leegarner.com>
 * @package     qrcode
 * @version     v1.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
use qrCode\Config;


/**
 * Perform the upgrade of the QRCode plugin.
 *
 * @return  boolean     True on success, False on error
 */
function QRC_upgrade()
{
    global $_TABLES, $_PLUGIN_INFO;

    if (isset($_PLUGIN_INFO[Config::PI_NAME])) {
        if (is_array($_PLUGIN_INFO[Config::PI_NAME])) {
            // glFusion >= 1.6.6
            $installed_ver = $_PLUGIN_INFO[Config::PI_NAME]['pi_version'];
        } else {
            // legacy
            $installed_ver = $_PLUGIN_INFO[Config::PI_NAME];
        }
    } else {
        return false;
    }
    $code_version = plugin_chkVersion_qrcode();
    if ($installed_version == $code_version) return true;

    if (!COM_checkVersion($installed_version, '1.0.1')) {
        $installed_version = '1.0.1';
        if (!QRC_set_version($installed_version)) return false;
    }

    // Update any config changes
    USES_lib_install();
    global $qrcodeConfigItems;
    require_once __DIR__ . '/install_defaults.php';
    _update_config('qrcode', $qrcodeConfigItems);

    // Final version in case there was no actual upgrade done
    if (!COM_checkVersion($installed_version, $code_version)) {
        if (!QRC_set_version($code_version)) return false;
    }
    return true;
}


/**
 * Update the plugin version number in the database.
 *
 * @param   string  $ver    New version
 * @return  boolean         True on success, False on failure
 */
function QRC_set_version($ver)
{
    global $_TABLES;

    $sql = "UPDATE {$_TABLES['plugins']} SET
                pi_version = '$ver',
                pi_gl_version = '" . Config::get('gl_version') . "
            WHERE pi_name = ') . " . Config::PI_NAME . "'";
    DB_query($sql, 1);
    if (DB_error()) {
        COM_errorLog("Error updating " . Config::get('pi_display_name') . " to version $ver");
        return false;
    } else {
        COM_errorLog(Config::get('pi_display_name') . " plugin was successfully updated to version $ver.");
        return true;
    }
}


/**
 * Remove deprecated files
 * Errors in unlink() and rmdir() are ignored.
 */
function QRC_remove_old_files()
{
    global $_CONF;

    $paths = array(
        // private/plugins/shop
        __DIR__ => array(
            // 1.1.0
            'classes/qrCode.class.php',
            'language/english.php',
        ),
        // public_html/shop
        $_CONF['path_html'] . 'qrcode' => array(
        ),
        // admin/plugins/shop
        $_CONF['path_html'] . 'admin/plugins/qrcode' => array(
        ),
    );

    foreach ($paths as $path=>$files) {
        foreach ($files as $file) {
            COM_errorLog("qrCode upgrade: removing $path/$file");
            @unlink("$path/$file");
        }
    }
}

