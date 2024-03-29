<?php
/**
 * Display cached images.
 *  
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2012-2022 Lee Garner <lee@leegarner.com>
 * @package     qrCode
 * @version     v1.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php 
 *              GNU Public License v2 or later
 */

/**
 * Include require glFusion common functions
 */
require_once '../lib-common.php';

$img = isset($_GET['img']) ? $_GET['img'] : '';
// Make sure the image exists
if ($img == '' || !qrCode\Code::exists($img)) {
    die('');
}
use qrCode\Config;

$file_path = Config::get('img_path') . $img;
$fsize = @filesize($file_path);
if ($fsize === FALSE) die('');

// Start the download
// set headers
header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: public');
header('Content-Description: File Transfer');
header('Content-Type: ' . qrCode\Code::MimeType($img));
header('Content-Disposition: attachment; filename="' . $img . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . $fsize);

// download
// @readfile($file_path);
$file = @fopen($file_path, 'rb');
if ($file) {
  while (!feof($file)) {
    print (fread($file, 1024*8));
    flush();
    //if (connection_status()!=0) {
    //  @fclose($file);
    //  die();
    //}
  }
  @fclose($file);
}

