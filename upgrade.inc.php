<?php
/**
*   Perform upgrade functions for QRCode
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2016 Lee Garner <lee@leegarner.com>
*   @package    qrcode
*   @version    1.0.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

/**
*   Perform the upgrade of the QRCode plugin.
*/
function QRC_upgrade()
{
    global $_TABLES, $_QRC_CONF, $_PLUGIN_INFO;

    $pi_name = $_QRC_CONF['pi_name'];
    $installed_version = $_PLUGIN_INFO[$pi_name];
    $code_version = plugin_chkVersion_qrcode();
    if ($installed_version == $code_version) return true;

    // update the version numbers
    $sql = "UPDATE {$_TABLES['plugins']} SET
                pi_version = '$code_version',
                pi_gl_version = '{$_QRC_CONF['gl_version']}'
            WHERE pi_name = '$pi_name'");
    DB_query($sql, 1);
    if (DB_error()) {
        COM_errorLog("Error updating $pi_name to version $code_version");
        return false;
    } else {
        COM_errorLog("$pi_name plugin was successfully updated to version $code_version.");
        return true;
    }
}

?>
