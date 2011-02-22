<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Qrcode Plugin for Geeklog                                                 |
// +---------------------------------------------------------------------------+
// | plugins/qrcode/install_defaults.php                                       |
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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'install_defaults.php') !== false) {
    die('This file can not be used on its own!');
}

/*
 * Qrcode default settings
 *
 * Initial Installation Defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 *
 */

global $_QRC_CONF_DEFAULT;

$_QRC_CONF_DEFAULT['module_size'] = 2;
$_QRC_CONF_DEFAULT['ecc_level']   = 'M';
$_QRC_CONF_DEFAULT['image_type']  = 'png';


/**
* Initialize Crcode Plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist. Initial values will be taken from $_QRC_CONF if available (e.g. from
* an old config.php), uses $_QRC_CONF_DEFAULT otherwise.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_qrcode()
{
    global $_CONF, $_QRC_CONF, $_QRC_CONF_DEFAULT;

    if (is_array($_QRC_CONF) && (count($_QRC_CONF) > 1)) {
        $_QRC_CONF_DEFAULT = array_merge($_QRC_CONF_DEFAULT, $_QRC_CONF);
    }

    $c = config::get_instance();
    $n = 'qrcode';
    $o = 0;
    if ($c->group_exists($n)) return true;
    $c->add('sg_main',     NULL,                              'subgroup', 0, 0, NULL, 0,    true, $n);
    // ----------------------------------
    $c->add('fs_main',     NULL,                              'fieldset', 0, 0, NULL, 0,    true, $n);
    $c->add('module_size', $_QRC_CONF_DEFAULT['module_size'], 'text',     0, 0, NULL, $o++, true, $n);
    $c->add('ecc_level',   $_QRC_CONF_DEFAULT['ecc_level'],   'select',   0, 0, 31,   $o++, true, $n);
    $c->add('image_type',  $_QRC_CONF_DEFAULT['image_type'],  'select',   0, 0, 30,   $o++, true, $n);

    return true;
}

?>