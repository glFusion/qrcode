<?php
/**
*   API functions for creating QR Codes.
*   Based on the Geeklog plugin by Yoshinori Tahara
*
*   @author     Lee Garner <lee@leegarner.com>
*   @author     Yoshinori Tahara <taharaxp@gmail.com>
*   @copyright  Copyright (c) 2010-2016 Lee Garner <lee@leegarner.com>
*   @copyright  2010 Yoshinori Tahara - dengen - taharaxp AT gmail DOT com
*   @package    qrcode
*   @version    1.0.0
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

// this file can't be used on its own
if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

$_QRC_CONF = array();

/** Include the glFusion configuration class if not done already */
require_once $_CONF['path_system'] . 'classes/config.class.php';

$qrc_config = config::get_instance();
$_QRC_CONF = $qrc_config->get_config('qrcode');

require_once dirname(__FILE__) . '/qrcode.php';

define('QRC_PI_PATH', $_CONF['path'] . 'plugins/' . $_QRC_CONF['pi_name']);
define('QRC_URL', $_CONF['site_url'] . '/' . $_QRC_CONF['pi_name']);
define('QRC_ADMIN_URL', $_CONF['site_admin_url'] . '/plugins/' . $_QRC_CONF['pi_name']);
define('QRC_CLASSPATH', QRC_PI_PATH . '/classes');

/** Include the user-supplied configuration file, if any */
if (is_file(QRC_PI_PATH . '/config.php')) {
    include_once QRC_PI_PATH . '/config.php';
}

/** Language file include */
$plugin_path = $_CONF['path'] . 'plugins/qrcode/';
$langfile = $plugin_path . 'language/' . $_CONF['language'] . '.php';
if (file_exists($langfile)) {
    require_once $langfile;
} else {
    require_once $plugin_path . 'language/english.php';
}

function USES_qrcode_class_qrcode()
{   include_once QRC_CLASSPATH . '/qrcode.class.php';   }


/**
*   Returns the current plugin code version
*
*   @return string  plugin version
*/
function plugin_chkVersion_qrcode()
{
    global $_QRC_CONF;
    return $_QRC_CONF['pi_version'];
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
*   Implements the [qrcode:] autotag.
*
*   @param    string  $op         operation to perform
*   @param    string  $content    item (e.g. story text), including the autotag
*   @param    array   $autotag    parameters used in the autotag
*   @param    mixed               tag names (for $op='tagname') or formatted content
*
*/
function plugin_autotags_qrcode($op, $content = '', $autotag = '')
{
    global $_CONF;

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
        $params['module_size'] = $_QRC_CONF['module_size'];
        $params['image_type'] = $_QRC_CONF['image_type'];
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
            $block = QRC_html(QRC_qrcode($params));
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

/*function QRC_qrcode(&$params)
{
    USES_qrcode_class_qrcode();
    return qrCode::MakeCode($params);
}*/


/**
*   Create the QR Code.
*   Creates the image file and returns the <img> tag to display the image.
*
*   @param  array   $params     Parameters. 'data' element is required.
*   @return string      Filename of code image. Call QRC_path() or QRC_html() to get paths.
*/
function QRC_qrcode(&$params)
{
    global $_CONF, $_QRC_CONF;

    // Call the cache cleaning function.
    if ($_QRC_CONF['cache_clean_interval'] >= 0) {
        QRC_cleanCache();
    }

    if (empty($params['image_type'])) {
        $params['image_type'] = $_QRC_CONF['image_type'];
    }
    if ($params['image_type'] == 'jpeg' || $params['image_type'] == 'jpg') {
        $params['image_type'] = 'jpeg';
    }else {
        $params['image_type'] = 'png';
    }
    
    $ext = ($params['image_type'] == 'jpeg') ? '.jpg' : '.png';

    if (empty($params['module_size'])) {
        $params['module_size'] = $_QRC_CONF['module_size'];
    }

    if (empty($params['ecc_level'])) {
        $params['ecc_level'] = $_QRC_CONF['ecc_level'];
    }
    $params['version'] = QRC_getVersion(1, $params['ecc_level'], $params['data']);

    $md5 = md5($params['data'] . 't='.$params['image_type']
               . 's='.$params['module_size'] . 'e='.$params['ecc_level']);

    $filename = $md5 . $ext;
    $filepath = $_QRC_CONF['img_path'];
    $filespec = $filepath . $filename;

    if (!file_exists($filespec)) {
        if (!is_writable($filepath)) {
            $html = '<div>Can not write the QR code image.</div>'.LB;
            return $html;
        }
        include_once $_CONF['path'] . 'plugins/qrcode/include/functions.php';
        $params['image_filename'] = $filespec;
        QRC_createQRImage($params);
    }

    /*$size = $params['module_size'] * 25 + $params['version'] * ($params['module_size'] * 4);
    $html = '<div class="qrcode">' . LB
          . '<img alt="QR code" width="' . $size . '" height="' . $size
          . '" src="' . $_CONF['site_url'] . '/qrcode/images/' . $filename . '"' . XHTML . '>' . LB
          . '</div>' . LB;
    */
    /*$html = '<img alt="QR code" width="' . $size . '" height="' . $size .
          '" src="' . QRC_URL . '/img.php?img=' .$filename . '" />';
    */
    return $filename;
}


/**
*   Get the URL to a QRCode image.
*   This returns only the image URL, leaving it up to to the caller
*   to create the complete image tag.
*
*   @param  string  $filename   Image filename
*   @return string      URL to render the image
*/
function QRC_url($filename)
{
    return QRC_URL . '/img.php?img=' . $filename;
}


/**
*   Get the HTML for a QRCode image.
*   Makes some assumptions about the image size, use
*   QRC_url() by itself to get just the image URL.
*
*   @uses   QRC_url()
*   @param  string  $filename   Image filename
*   @return string      Complete image tag for the URL
*/
function QRC_html($filename)
{
    global $_QRC_CONF;

    $size = $_QRC_CONF['module_size'] * 50;
    $html = '<img alt="QR code" width="' . $size . 
            '" height="' . $size . '" src="' . QRC_url($filename) '" />';
    return $html;
}


/**
*   Get the actual path to a QRCode image file
*
*   @param  string  $filename   Image filename
*   @return string          Full path to the image file
*/
function QRC_path($filename)
{
    global $_QRC_CONF;

    return $_QRC_CONF['img_path'] . $filename;
}


/**
*   Clean out old QR code images.
*/
function QRC_cleanCache()
{
    global $_QRC_CONF;

    if ($_QRC_CONF['cache_clean_interval'] < 0) {
        // No cache cleaning required
        return false;
    }
    $lastCleanFile = $_QRC_CONF['img_path'] . 'qrc_cacheLastCleanTime.touch';
        
    //If this is a new timthumb installation we need to create the file
    if (!is_file($lastCleanFile)) {
        if (!touch($lastCleanFile)) {
            COM_errorLog("QRCODE: Cannot touch cache clean file $lastCleanFile");
        }
        return false;
    }

    $cache_clean_interval = $_QRC_CONF['cache_clean_interval'] * 60;  // minutes
    $cache_max_age = $_QRC_CONF['cache_max_age'] * 86400; // days
    if (@filemtime($lastCleanFile) < (time() - $cache_clean_interval)) {
        //Cache was last cleaned more than FILE_CACHE_TIME_BETWEEN_CLEANS ago
        if (!touch($lastCleanFile)) {
            $this->error("Could not create cache clean timestamp file.");
        }
        $files = glob($_QRC_CONF['img_path'] . '*.' . $_QRC_CONF['image_type']);
        if ($files) {
            $timeAgo = time() - $cache_max_age;
            foreach ($files as $file) {
                if (@filemtime($file) < $timeAgo) {
                    @unlink($file);
                }
            }
        }
        return true;
    }
    return false;
}


/**
*   Show the site URL in a block as a QR Code
*
*   @return string  Image URL
*/
function phpblock_qrcode()
{
    $params = array(
        'data' => COM_buildURL(COM_getCurrentURL()),
        'module_size' => '3',
    );
    return QRC_html(QRC_qrcode($params));
}


/**
*   Service function to allow plugins to get a QR code
*   $output is an array populated with useful string values for the image:
*       - img  => Image filename
*       - path => Filesystem path to the image
*       - html => Full HTML "img src" tag using default sizes and styles
*       - url  => URL only to the image to allow styling by the caller
*
*   @param  array   $args       Arguments, 'data' element is required.
*   @param  mixed   &$output    Receives the QR code URL string
*   @param  mixed   &$svc_msg   Not used
*   @return integer     Status code (PLG_RET_OK, PLG_RET_ERROR).
*/
function service_getcode_qrcode($args, &$output, &$svc_msg)
{
    if (!is_array($args)) return PLG_RET_ERROR;
    if (!isset($args['data'])) return PLG_RET_ERROR;
    $output = array();
    $output['img']  = QRC_qrcode($args);
    $output['path'] = QRC_path($output['img']);
    $output['html'] = QRC_html($output['img']);
    $output['url']  = QRC_url($output['img']);
    return PLG_RET_OK;
}

?>
