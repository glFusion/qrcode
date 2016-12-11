<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Qrcode Plugin for Geeklog                                                 |
// +---------------------------------------------------------------------------+
// | plugins/qrcode/language/english.php                                       |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2010 by the following authors:                              |
// |                                                                           |
// | Authors: Yoshinori Tahara - dengen - taharaxp AT gmail DOT com            |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+

global $LANG_postmodes;

$LANG_QRC = array(
    'plugin_name'       => 'QRcode',
);

// Messages for the plugin upgrade
$PLG_qrcode_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_qrcode_MESSAGE3002 = $LANG32[9];

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
