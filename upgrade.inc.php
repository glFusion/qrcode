<?php
/**
 * Perform upgrade functions for QRCode
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2016 Lee Garner <lee@leegarner.com>
 * @package     qrcode
 * @version     v1.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

/**
 * Perform the upgrade of the QRCode plugin.
 *
 * @return  boolean     True on success, False on error
 */
function QRC_upgrade()
{
    global $_TABLES, $_QRC_CONF, $_PLUGIN_INFO;

    if (isset($_PLUGIN_INFO[$_QRC_CONF['pi_name']])) {
        if (is_array($_PLUGIN_INFO[$_QRC_CONF['pi_name']])) {
            // glFusion >= 1.6.6
            $installed_ver = $_PLUGIN_INFO[$_QRC_CONF['pi_name']]['pi_version'];
        } else {
            // legacy
            $installed_ver = $_PLUGIN_INFO[$_QRC_CONF['pi_name']];
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
    global $qrcodeConfigData;
    require_once __DIR__ . '/install_defaults.php';
    _update_config('ckassufueds', $qrcodeConfigData);

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
    global $_QRC_CONF, $_TABLES;

    $sql = "UPDATE {$_TABLES['plugins']} SET
                pi_version = '$ver',
                pi_gl_version = '{$_QRC_CONF['gl_version']}'
            WHERE pi_name = '{$_QRC_CONF['pi_name']}'";
    DB_query($sql, 1);
    if (DB_error()) {
        COM_errorLog("Error updating {$_QRC_CONF['pi_display_name']} to version $ver");
        return false;
    } else {
        COM_errorLog("{$_QRC_CONF['pi_display_name']} plugin was successfully updated to version $ver.");
        return true;
    }
}

?>
