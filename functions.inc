<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Qrcode Plugin for Geeklog                                                 |
// +---------------------------------------------------------------------------+
// | plugins/qrcode/functions.inc                                              |
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

// this file can't be used on its own
if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.inc') !== false) {
    die ('This file can not be used on its own.');
}

$_QRC_CONF = array();

/**
 * Language file include
 */
$plugin_path = $_CONF['path'] . 'plugins/qrcode/';
$langfile = $plugin_path . 'language/' . $_CONF['language'] . '.php';
if (file_exists($langfile)) {
    require_once $langfile;
} else {
    require_once $plugin_path . 'language/english.php';
}

/**
* Check and see if we need to load the plugin configuration
*/
require_once $_CONF['path_system'] . 'classes/config.class.php';
$plg_config = config::get_instance();
$temp = $plg_config->get_config('qrcode');
if (is_array($temp)) {
    $_QRC_CONF = array_merge($_QRC_CONF, $temp);
}

// +---------------------------------------------------------------------------+
// | Geeklog Plugin API Implementation                                         |
// +---------------------------------------------------------------------------+

/**
 * Returns the current plugin code version
 *
 * @return string    plugin version
 */
function plugin_chkVersion_qrcode()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/qrcode/autoinstall.php';
    $inst_parms = plugin_autoinstall_qrcode('qrcode');

    return $inst_parms['info']['pi_version'];
}

/**
* Automatic uninstall function for plugins
*
* @return   array
*/
function plugin_autouninstall_qrcode()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/qrcode/autoinstall.php';

    return QRC_autouninstall();
}

/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_qrcode()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/qrcode/autoinstall.php';

    return QRC_upgrade();
}

/**
* Implements the [qrcode:] autotag.
*
* @param    string  $op         operation to perform
* @param    string  $content    item (e.g. story text), including the autotag
* @param    array   $autotag    parameters used in the autotag
* @param    mixed               tag names (for $op='tagname') or formatted content
*
*/
function plugin_autotags_qrcode($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES;

    if ($op == 'tagname') {
        return 'qrcode';
    } else if ($op == 'parse') {
        $params = array();
        switch ($autotag['parm1']) {
            case 'home':
            case 'top':
                $params['data'] = $_CONF['site_url'];
                break;
            case 'current':
                $params['data'] = COM_buildURL(COM_getCurrentURL());
                break;
            default:
                $params['data'] = COM_applyFilter($autotag['parm1']);
                break;
        }
        if (!empty($autotag['parm2'])) {
            $parm2 = explode(' ', COM_applyFilter($autotag['parm2']));
            foreach ($parm2 as $p) {
                list($var,$val) = explode('=', $p);
                switch ($var) {
                    case 's':
                    case 'size':
                        $params['module_size'] = $val;
                        break;
                    case 't':
                    case 'type':
                        $params['image_type'] = $val;
                        break;
                    case 'e':
                    case 'ecc':
                        $params['ecc_level'] = $val;
                        break;
                    default:
                        // nothing to do
                        break;
                }
            }
        }

        $block = '';
        if (!empty($params['data'])) {
            $block = QRC_qrcode($params);
        }
        $content = str_replace($autotag['tagstr'], $block, $content);

        return $content;
    }
}


function QRC_getVersion($version, $ecc_level, $data) {

    if (empty($version)) $version = 1;
    $counter = 0;
    $data_bits[$counter++] = 4;
    $length = strlen($data);
    $codeword_num_plus = array(0,0,0,0,0,0,0,0,0,0,
        2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
        4,4,4,4,4,4,4,4,4,4,4,4,4,4);

    /* determine encode mode */
    if (preg_match("/[^0-9]/", $data)) {
        if (preg_match("/[^0-9A-Z \$\*\%\+\-\.\/\:]/", $data)) {
             /* 8bit byte mode */
            $codeword_num_plus = array(0,0,0,0,0,0,0,0,0,0,
                8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,
                8,8,8,8,8,8,8,8,8,8,8,8,8,8);
            $data_bits[$counter++] = 8; /* #version 1-9 */
            $i = 0;
            while ($i < $length) {
                $data_bits[$counter++] = 8;
                $i++;
            }
        } else {
            /* alphanumeric mode */
            $data_bits[$counter++] = 9; /* #version 1-9 */
            $i = 0;
            while ($i < $length) {
                if (($i % 2) == 0) {
                    $data_bits[$counter] = 6;
                } else {
                    $data_bits[$counter++] = 11;
                }
                $i++;
            }
        }
    } else {
        /* numeric mode */
        $data_bits[$counter++] = 10; /* #version 1-9 */
        $i = 0;
        while ($i < $length) {
            if (($i % 3) == 0) {
                $data_bits[$counter] = 4;
            } else {
                if (($i % 3) == 1) {
                    $data_bits[$counter] = 7;
                } else {
                    $data_bits[$counter++] = 10;
                }
            }
            $i++;
        }
    }
    $total_data_bits = array_sum($data_bits);

    $ecc_hash = array("L"=>"1", "M"=>"0", "Q"=>"3", "H"=>"2");
    $ec = @$ecc_hash[strtoupper($ecc_level)];
    if (!$ec) $ec = 0;

    $max_data_bits_array = array (
        0,128,224,352,512,688,864,992,1232,1456,1728,
        2032,2320,2672,2920,3320,3624,4056,4504,5016,5352,
        5712,6256,6880,7312,8000,8496,9024,9544,10136,10984,
        11640,12328,13048,13800,14496,15312,15936,16816,17728,18672,

        152,272,440,640,864,1088,1248,1552,1856,2192,
        2592,2960,3424,3688,4184,4712,5176,5768,6360,6888,
        7456,8048,8752,9392,10208,10960,11744,12248,13048,13880,
        14744,15640,16568,17528,18448,19472,20528,21616,22496,23648,

        72,128,208,288,368,480,528,688,800,976,
        1120,1264,1440,1576,1784,2024,2264,2504,2728,3080,
        3248,3536,3712,4112,4304,4768,5024,5288,5608,5960,
        6344,6760,7208,7688,7888,8432,8768,9136,9776,10208,

        104,176,272,384,496,608,704,880,1056,1232,
        1440,1648,1952,2088,2360,2600,2936,3176,3560,3880,
        4096,4544,4912,5312,5744,6032,6464,6968,7288,7880,
        8264,8920,9368,9848,10288,10832,11408,12016,12656,13328);

    /* auto version select */
    $i = $version + 40 * $ec;
    $j = $i + 39;
    while ($i <= $j) {
        if ($max_data_bits_array[$i] >=
            ($total_data_bits + $codeword_num_plus[$version])) break;
        $i++;
        $version++;
    }

    return $version;
}

function QRC_qrcode(&$params)
{
    global $_CONF, $_QRC_CONF;

    if (empty($params['image_type'])) {
        $params['image_type'] = $_QRC_CONF['image_type'];
    }
    if ($params['image_type'] == 'jpeg' || $params['image_type'] == 'jpg') {
        $params['image_type'] = 'jpeg';
    }else {
        $params['image_type'] = 'png';
    }
    
    $md5 = md5($params['data']);
    $ext = ($params['image_type'] == 'jpeg') ? '.jpg' : '.png';

    if (empty($params['module_size'])) {
        $params['module_size'] = $_QRC_CONF['module_size'];
    }

    if (empty($params['ecc_level'])) {
        $params['ecc_level'] = $_QRC_CONF['ecc_level'];
    }

    $md5 = md5($params['data'] . 't='.$params['image_type']
               . 's='.$params['module_size'] . 'e='.$params['ecc_level']);

    $filename = $md5 . $ext;
    $file_path = $_CONF['path_html'] . 'qrcode/images/' . $filename;

    if (!file_exists($file_path)) {
        if (!is_writable($_CONF['path_html'] . 'qrcode/images/')) {
            $html = '<div>Can not write the QR code image.</div>'.LB;
            return $html;
        }
        include_once $_CONF['path'] . 'plugins/qrcode/include/functions.php';
        $params['image_filename'] = $file_path;
        QRC_createQRImage($params);
    }
    $params['version'] = QRC_getVersion(1, $params['ecc_level'], $params['data']);
    $size = $params['module_size'] * 25 + $params['version'] * ($params['module_size'] * 4);
    $html = '<div class="qrcode">' . LB
          . '<img alt="QR code" width="' . $size . '" height="' . $size
          . '" src="' . $_CONF['site_url'] . '/qrcode/images/' . $filename . '"' . XHTML . '>' . LB
          . '</div>' . LB;

    return $html;
}

function phpblock_qrcode()
{
    $params= array();
    $params['data'] = COM_buildURL(COM_getCurrentURL());
    return QRC_qrcode($params);
}

?>
