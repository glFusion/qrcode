<?php
//  $Id$
/**
*   Create configuration items for the QR Code plugin
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

$_QRC_CONF_DEFAULT = array(
    'module_size'   => 2,
    'ecc_level'     => 'M',
    'image_type'    => 'png',
);


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
    $pi = 'qrcode';
    if ($c->group_exists($pi)) return true;

    $c->add('sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, $pi);
    $c->add('fs_main', NULL, 'fieldset', 0, 0, NULL, 0, true, $pi);
    $c->add('module_size', $_QRC_CONF_DEFAULT['module_size'], 'text',
        0, 0, NULL, 10, true, $pi);
    $c->add('ecc_level', $_QRC_CONF_DEFAULT['ecc_level'], 'select',
        0, 0, 31, 20, true, $pi);
    $c->add('image_type', $_QRC_CONF_DEFAULT['image_type'], 'select',
        0, 0, 30, 30, true, $pi);

    return true;
}

?>
