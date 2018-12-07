<?php
/**
 * Create configuration items for the QR Code plugin.
 * Based on the Geeklog plugin by Yoshinori Tahara.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @author      Yoshinori Tahara <taharaxp@gmail.com>
 * @copyright   Copyright (c) 2010-2014 Lee Garner <lee@leegarner.com>
 * @package     qrcode
 * @version     v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */

if (strpos(strtolower($_SERVER['PHP_SELF']), 'install_defaults.php') !== false) {
    die('This file can not be used on its own!');
}

/*
 * Qrcode default settings.
 *
 * Initial Installation Defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 * @var array
 */

$qrcodeConfigItems = array(
    array(
        'name' => 'sg_main',
        'default_value' => NULL,
        'type' => 'subgroup',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'qrcode',
    ),
    array(
        'name' => 'fs_main',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => true,
        'group' => 'qrcode',
    ),
    array(
        'name' => 'module_size',
        'default_value' => '2',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 10,
        'set' => true,
        'group' => 'qrcode',
    ),
    array(
        'name' => 'ecc_level',
        'default_value' => 'M',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 31,
        'sort' => 20,
        'set' => true,
        'group' => 'qrcode',
    ),
    array(
        'name' => 'image_type',
        'default_value' => 'png',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 30,
        'sort' => 30,
        'set' => true,
        'group' => 'qrcode',
    ),
    array(
        'name' => 'cache_max_age',
        'default_value' => '90',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 40,
        'set' => true,
        'group' => 'qrcode',
    ),
    array(
        'name' => 'cache_clean_interval',
        'default_value' => '120',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 50,
        'set' => true,
        'group' => 'qrcode',
    ),
);

/**
 * Initialize QRcode Plugin configuration.
 * Creates the database entries for the configuation if they don't already exist.
 *
 * @return  boolean     true: success; false: an error occurred
 */
function plugin_initconfig_qrcode()
{
    global $qrcodeConfigData;

    $c = config::get_instance();
    if (!$c->group_exists('qrcode')) {
        USES_lib_install();
        foreach ($qrcodeConfigData AS $cfgItem) {
            _addConfigItem($cfgItem);
        }
    }
    return true;
}

?>
