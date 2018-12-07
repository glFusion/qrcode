<?php
/**
 * Language file for the QRCode plugin.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2009-2018 Lee Garner <lee@leegarner.com>
 * @package     qrcode
 * @version     v1.0.5
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */


/** Global array to hold all plugin-specific configuration items.
    @global $LANG_QRC
    @var array */
$LANG_QRC = array(
    'plugin_name'       => 'QRcode',
);

// Messages for the plugin upgrade
$PLG_qrcode_MESSAGE3001 = 'Plugin upgrade not supported.';
if (isset($LANG32)) {
    $PLG_qrcode_MESSAGE3002 = $LANG32[9];
}
// Localization of the Admin Configuration UI
$LANG_configsections['qrcode'] = array(
    'label' => 'QRcode',
    'title' => 'QRcode Configuration'
);  

$LANG_confignames['qrcode'] = array(
    'module_size'        => 'Module Size',
    'ecc_level'          => 'ECC Level',
    'image_type'         => 'Image Type',
    'cache_max_age'     => 'Cache Max Age',
    'cache_clean_interval' => 'Cache Clean Interval',
);

$LANG_configsubgroups['qrcode'] = array(
    'sg_main'           => 'Main Settings',
);

$LANG_fs['qrcode'] = array(
    'fs_main'           => 'General QRcode Settings',
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['qrcode'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
    30 => array('PNG' => 'png', 'JPEG' => 'jpg'),
    31 => array('Level L' => 'L', 'Level M' => 'M', 'Level Q' => 'Q', 'Level H' => 'H'),
);

?>
