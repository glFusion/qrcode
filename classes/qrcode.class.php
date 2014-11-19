<?php
//  $Id$
/**
*   Class to handle creating qrcodes
*
*   @author     Lee Garner <lee@leegarner.com>
*   @author     Yoshinori Tahara <taharaxp@gmail.com>
*   @copyright  Copyright (c) 2010-2012 Lee Garner <lee@leegarner.com>
*   @copyright  2010 Yoshinori Tahara - dengen - taharaxp AT gmail DOT com
*   @package    qrcode
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/


/**
*   Class to handle qrcodes
*   @package    qrcode
*/
class qrCode
{

    private static function QRC_getVersion($version, $ecc_level, $data)
    {

        if (empty($version)) $version = 1;
        $counter = 0;
        $data_bits[$counter++] = 4;
        $length = strlen($data);
        $codeword_num_plus = array(0,0,0,0,0,0,0,0,0,0,
            2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
            4,4,4,4,4,4,4,4,4,4,4,4,4,4);

        // determine encode mode
        if (preg_match("/[^0-9]/", $data)) {
            if (preg_match("/[^0-9A-Z \$\*\%\+\-\.\/\:]/", $data)) {
                // 8bit byte mode
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
                ($total_data_bits + $codeword_num_plus[$version]))
                break;
            $i++;
            $version++;
        }

        return $version;
    }

    public static function MakeCode(&$params)
    {
        global $_CONF, $_QRC_CONF;

        if (empty($params['image_type'])) {
            $params['image_type'] = $_QRC_CONF['image_type'];
        }
        if ($params['image_type'] == 'jpeg' || $params['image_type'] == 'jpg') {
            $params['image_type'] = 'jpeg';
        } else {
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
        $params['version'] =
                QRC_getVersion(1, $params['ecc_level'], $params['data']);
        $size = ($params['module_size'] * 25) +
                ($params['version'] * ($params['module_size'] * 4));
        $html = '<div class="qrcode">' . LB
              . '<img alt="QR code" width="' . $size . '" height="' . $size
              . '" src="' . QRC_URL . '/img.php?img=' . $filename . '"' . '/>' . LB
              . '</div>' . LB;

        return $html;
    }


    public static function file_exists($filename)
    {
        global $_CONF, $_QRC_CONF;
        if (file_exists($_QRC_CONF['img_path'] . $filename)) {
            return true;
        } else {
            return false;
        }
    }


    public static function MimeType()
    {
        global $_QRC_CONF;
        switch ($_QRC_CONF['image_type']) {
        case 'jpg':
            return 'image/jpeg';
            break;
        case 'png':
            return 'image/png';
            break;  
        }
    }

}

?>
