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
$PLG_qrcode_MESSAGE3001 = 'プラグインのアップグレードはサポートされていません。';
if (isset($LANG32)) {
    $PLG_qrcode_MESSAGE3002 = $LANG32[9];
}

// Localization of the Admin Configuration UI
$LANG_configsections['qrcode'] = array(
    'label' => 'QRコード',
    'title' => 'QRコードの設定'
);

$LANG_confignames['qrcode'] = array(
    'module_size'        => 'モジュールサイズ',
    'ecc_level'          => 'エラー補正レベル',
    'image_type'         => '画像のタイプ',
    'cache_max_age'     => 'Cache Max Age',
    'cache_clean_interval' => 'Cache Clean Interval',
);

$LANG_configsubgroups['qrcode'] = array(
    'sg_main'           => 'メイン',
);

$LANG_fs['qrcode'] = array(
    'fs_main'           => 'QRコードのメイン設定',
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['qrcode'] = array(
    0 => array('はい' => 1, 'いいえ' => 0),
    1 => array('はい' => TRUE, 'いいえ' => FALSE),
    12 => array('アクセス不可' => 0, '表示' => 2, '表示・編集' => 3),
    30 => array('PNG' => 'png', 'JPEG' => 'jpg'),
    31 => array('レベルL' => 'L', 'レベルM' => 'M', 'レベルQ' => 'Q', 'レベルH' => 'H'),
);

?>
