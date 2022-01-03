<?php
/**
 * Class to handle creating qrcodes.
 * Adapted from the qrCode plugin for Geekog by Yoshinori Tahara,
 * which uses code from Y.Swetake.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @author      Yoshinori Tahara <taharaxp@gmail.com>
 * @author      Y.Swetake
 * @copyright   Copyright (c) 2010-2020 Lee Garner <lee@leegarner.com>
 * @copyright   2010 Yoshinori Tahara - dengen - taharaxp AT gmail DOT com
 * @copyright   version 0.50g (C)2000-2005,Y.Swetake
 * @package     qrcode
 * @version     v1.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace qrCode;


/**
 * Class to handle qrcodes.
 * @package qrcode
 */
class QR extends Code
{
    /** Image type, JPEG or PNG.
     * @var string */
    private $image_type = 'jpg';

    /** Error Correction level.
     * @var integer */
    private $ecc_level = 'M';

    /** Error correction code, based on ecc_level.
     * @var integer */
    private $ec = 0;

    /** Module size.
     * @var integer */
    private $module_size = 8;

    /** Version number.
     * @var integer */
    private $version = 1;

    /** Max data bits array, used by a couple of functions.
     * @var array */
    private $max_data_bits_array = array(
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
        8264,8920,9368,9848,10288,10832,11408,12016,12656,13328,
    );


    /**
     * Constructor. Instantiate a qrCode object based on suppleid parameters.
     * The params array must contain at least a 'data' element with the
     * data to be encoded.
     *
     * @param   array   $params     Array of parameters for qrCode creation
     */
    public function __construct($params)
    {
        global $_QRC_CONF;

        if (!isset($params['data'])) {
            COM_errorLog("Missing data in constructor for " . __CLASS__);
            return;
        }
        $this->data = $params['data'];
        if (isset($params['module_size'])) {
            $this->setModuleSize($params['module_size']);
        }
        if (isset($params['ecc_level'])) {
            $this->setECCLevel($params['ecc_level']);
        }
        if (isset($params['image_type'])) {
            $this->setImageType($params['image_type']);
        }
        $this->setVersion();
        $md5 = md5(
            $this->data . 't=' . $this->image_type
            . 's=' . $this->module_size . 'e=' . $this->ecc_level
        );
        $this->filename = $md5 . '.' . $this->image_type;
        parent::__construct($params);
    }


    /**
     * Set the image type to render.
     *
     * @param   string  $value  Desired image type value
     * @return  object  $this
     */
    private function setImageType($value)
    {
        switch ($value) {
        case 'jpg':
        case 'jpeg':
            $this->image_type = 'jpg';
            break;
        case 'png':
        default:
            $this->image_type = 'png';
            break;
        }
        return $this;
    }


    /**
     * Set the error correction level.
     * The ECC level is a string, while `ec` is an integer
     * based on the string.
     *
     * @param   string  $value  ECC level indicator
     * @return  object  $this
     */
    private function setECCLevel($value)
    {
        $this->ecc_level = strtoupper($value);
        switch ($this->ecc_level) {
        case 'L':
            $this->ec = 1;
            break;
        case 'Q':
            $this->ec = 3;
            break;
        case 'H':
            $this->ec = 2;
            break;
        case 'M':
        default:
            $this->ec = 0;
            break;
        }
        return $this;
    }


    /**
     * Validate and set the module size.
     *
     * @param   integer $value  Module size to set.
     * @return  object  $this
     */
    private function setModuleSize($value)
    {
        if ($value < 1) {
            if ($this->image_type == 'jpg') {
                $this->module_size = 8;
            } else {
                $this->module_size = 2;
            }
        } else {
            $this->module_size = (int)$value;
        }
        return $this;
    }


    /**
     * Sets the qrCode version in an object var, and returns the value.
     *
     * @return  integer     qrCode version
     */
    private function setVersion()
    {
        $data_bits = array();
        $counter = 0;
        $data_bits[$counter++] = 4;
        $length = strlen($this->data);
        $codeword_num_plus = array(0,0,0,0,0,0,0,0,0,0,
            2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
            4,4,4,4,4,4,4,4,4,4,4,4,4,4);

        // determine encode mode
        if (preg_match("/[^0-9]/", $this->data)) {
            if (preg_match("/[^0-9A-Z \$\*\%\+\-\.\/\:]/", $this->data)) {
                // 8bit byte mode
                $codeword_num_plus = array(0,0,0,0,0,0,0,0,0,0,
                    8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,
                    8,8,8,8,8,8,8,8,8,8,8,8,8,8);
                $data_bits[$counter++] = 8; /* #version 1-9 */
                for ($i = 0; $i < $length; $i++) {
                    $data_bits[$counter++] = 8;
                }
            } else {
                /* alphanumeric mode */
                $data_bits[$counter++] = 9; /* #version 1-9 */
                for ($i = 0; $i < $length; $i++) {
                    if (($i % 2) == 0) {
                        $data_bits[$counter] = 6;
                    } else {
                        $data_bits[$counter++] = 11;
                    }
                }
            }
        } else {
            /* numeric mode */
            $data_bits[$counter++] = 10; /* #version 1-9 */
            for ($i = 0; $i < $length; $i++) {
                if (($i % 3) == 0) {
                    $data_bits[$counter] = 4;
                } else {
                    if (($i % 3) == 1) {
                    $data_bits[$counter] = 7;
                    } else {
                        $data_bits[$counter++] = 10;
                    }
                }
            }
        }
        $total_data_bits = array_sum($data_bits);

        $i = $this->version + 40 * $this->ec;
        $j = $i + 39;
        for (; $i <= $j; $i++, $this->version++) {
            if ($this->max_data_bits_array[$i] >=
                ($total_data_bits + $codeword_num_plus[$this->version]))
                break;
        }
        return $this->version;
    }


    /**
     * Get the HTML to display a qrCode image.
     * Creates the image if not already done.
     *
     * @uses    qrCode::getURL()
     * @param   array   $classes    Optional array of additional CSS classes
     * @return  string  HTML to display
     */
    public function getHTML($classes=array())
    {
        global $_QRC_CONF;

        if ($this->have_image || $this->createImage()) {
            if (is_array($classes) && !empty($classes)) {
                $cls = implode(' ', $classes);
            } else {
                $cls = '';
            }
            $size = ($this->module_size * 25) +
                ($this->version * ($this->module_size * 4));
            $html = '<div class="qrcode ' . $cls . '">' . LB
              . '<img alt="QR code" width="' . $size . '" height="' . $size
              . '" src="' . $this->getURL() . '"' . '/>' . LB
              . '</div>' . LB;
        } else {
            $html = '';
        }
        return $html;
    }


    /**
     * Create the qrCode image and save it in the cach directory.
     * Returns True if the file already exists.
     *
     * @return  boolean     True on success, False on failure
     */
    public function createImage()
    {
        // Already have this image and info in the current object
        // Used to minimize the effect of multiple calls within a
        // single session
        if ($this->have_image) return true;

        // Return false if GD library isn't available.
        if (!function_exists('imagecreate')) {
            return false;
        }

        // Call the cache cleaning function.
        self::cleanCache();

        if ($this->file_exists()) {
            // image file already exists
            return true;
        }
        if (!is_writable($this->filepath)) {
            // won't be able to write to the file
            COM_errorLog('Cannot write to directory ' . $this->filepath);
            return false;
        }

        $path       = QRC_PI_PATH . '/include/data';   // path to data files.
        $image_path = QRC_PI_PATH . '/include/images'; // path to QRcode frame images.
        $version_ul = 40;                    // upper limit for version
        $data_bits = array();
        $data_value = array();

        if ($this->module_size < 1) {
            if ($this->image_type == 'jpg') {
                $this->module_size = 8;
            } else {
                $this->module_size = 2;
            }
        }

        $data_length = strlen($this->data);
        if ($data_length <= 0) {
            COM_errorLog('QRcode : Data do not exist.');
            return false;
        }
        $data_counter = 0;
        $data_bits[$data_counter] = 4;

        //--- determine encode mode
        if (preg_match("/[^0-9]/",$this->data)) {
            if (preg_match("{[^0-9A-Z \$\*\%\+\-\.\/\:]}", $this->data)) {
                //--- 8bit byte mode
                $codeword_num_plus=array(0,0,0,0,0,0,0,0,0,0,
                    8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,
                    8,8,8,8,8,8,8,8,8,8,8,8,8,8);

                $data_value[$data_counter] = 4;
                $data_counter++;
                $data_value[$data_counter] = $data_length;
                $data_bits[$data_counter] = 8;   // #version 1-9
                $codeword_num_counter_value = $data_counter;
                $data_counter++;

                for ($i = 0; $i < $data_length; $i++) {
                    $data_value[$data_counter]=ord(substr($this->data, $i, 1));
                    $data_bits[$data_counter]=8;
                    $data_counter++;
                }
            } else {
                //--- alphanumeric mode
                $codeword_num_plus=array(0,0,0,0,0,0,0,0,0,0,
                    2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
                    4,4,4,4,4,4,4,4,4,4,4,4,4,4);

                $data_value[$data_counter] = 2;
                $data_counter++;
                $data_value[$data_counter] = $data_length;
                $data_bits[$data_counter] = 9;  // #version 1-9
                $codeword_num_counter_value = $data_counter;

                $alphanumeric_character_hash=array("0"=>0,"1"=>1,"2"=>2,"3"=>3,"4"=>4,
                    "5"=>5,"6"=>6,"7"=>7,"8"=>8,"9"=>9,"A"=>10,"B"=>11,"C"=>12,"D"=>13,"E"=>14,
                    "F"=>15,"G"=>16,"H"=>17,"I"=>18,"J"=>19,"K"=>20,"L"=>21,"M"=>22,"N"=>23,
                    "O"=>24,"P"=>25,"Q"=>26,"R"=>27,"S"=>28,"T"=>29,"U"=>30,"V"=>31,
                    "W"=>32,"X"=>33,"Y"=>34,"Z"=>35," "=>36,"$"=>37,"%"=>38,"*"=>39,
                    "+"=>40,"-"=>41,"."=>42,"/"=>43,":"=>44);

                $data_counter++;
                for ($i = 0; $i < $data_length; $i++) {
                    if (($i %2)==0) {
                        $data_value[$data_counter] = $alphanumeric_character_hash[substr($this->data, $i, 1)];
                        $data_bits[$data_counter] = 6;
                    } else {
                        $data_value[$data_counter] = $data_value[$data_counter]*45+$alphanumeric_character_hash[substr($this->data, $i, 1)];
                        $data_bits[$data_counter] = 11;
                        $data_counter++;
                    }
                }
            }
        } else {
            //--- numeric mode
            $codeword_num_plus=array(0,0,0,0,0,0,0,0,0,0,
                2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
                4,4,4,4,4,4,4,4,4,4,4,4,4,4);

            $data_value[$data_counter] = 1;
            $data_counter++;
            $data_value[$data_counter] = $data_length;
            $data_bits[$data_counter] = 10;    // version 1-9
            $codeword_num_counter_value = $data_counter;

            $data_counter++;
            for ($i = 0; $i < $data_length; $i++) {
                if (($i % 3) == 0) {
                    $data_value[$data_counter] = substr($this->data, $i, 1);
                    $data_bits[$data_counter] = 4;
                } else {
                    $data_value[$data_counter] = $data_value[$data_counter] * 10 + substr($this->data, $i, 1);
                    if ( ($i % 3) == 1) {
                         $data_bits[$data_counter] = 7;
                    } else {
                        $data_bits[$data_counter] = 10;
                        $data_counter++;
                    }
                }
            }
        }

        if (@$data_bits[$data_counter] > 0) {
            $data_counter++;
        }

        $total_data_bits=0;
        for ($i = 0; $i < $data_counter; $i++) {
            $total_data_bits += $data_bits[$i];
        }

        $max_data_bits = $this->max_data_bits_array[$this->version + 40 * $this->ec];
        if ($this->version > $version_ul){
            COM_errorLog('QRcode : too large version.');
            return false;
        }
        $total_data_bits += $codeword_num_plus[$this->version];
        $data_bits[$codeword_num_counter_value] += $codeword_num_plus[$this->version];

        $max_codewords_array=array(
            0,26,44,70,100,134,172,196,242,
            292,346,404,466,532,581,655,733,815,901,991,1085,1156,
            1258,1364,1474,1588,1706,1828,1921,2051,2185,2323,2465,
            2611,2761,2876,3034,3196,3362,3532,3706,
        );

        $max_codewords = $max_codewords_array[$this->version];
        $max_modules_1side = 17 + ($this->version <<2);

        $matrix_remain_bit=array(0,0,7,7,7,7,7,0,0,0,0,0,0,0,3,3,3,3,3,3,3,
            4,4,4,4,4,4,4,3,3,3,3,3,3,3,0,0,0,0,0,0);

        //--- read version ECC data file
        $byte_num = $matrix_remain_bit[$this->version]+($max_codewords << 3);
        $filename = $path . '/qrv' . $this->version . '_' . $this->ec . '.dat';
        $fp1 = fopen($filename, 'rb');
        $matx = fread($fp1, $byte_num);
        $maty = fread($fp1, $byte_num);
        $masks = fread($fp1, $byte_num);
        $fi_x = fread($fp1, 15);
        $fi_y = fread($fp1, 15);
        $rs_ecc_codewords = ord(fread($fp1, 1));
        $rso = fread($fp1, 128);
        fclose($fp1);

        $matrix_x_array = unpack('C*', $matx);
        $matrix_y_array = unpack('C*', $maty);
        $mask_array = unpack('C*', $masks);
        $rs_block_order = unpack('C*', $rso);

        $format_information_x2 = unpack('C*', $fi_x);
        $format_information_y2 = unpack('C*', $fi_y);

        $format_information_x1 = array(0,1,2,3,4,5,7,8,8,8,8,8,8,8,8);
        $format_information_y1 = array(8,8,8,8,8,8,8,8,7,5,4,3,2,1,0);

        $max_data_codewords = ($max_data_bits >>3);

        $filename = $path . '/rsc' . $rs_ecc_codewords . '.dat';
        $fp0 = fopen($filename, 'rb');
        for ($i = 0; $i < 256; $i++) {
            $rs_cal_table_array[$i] = fread($fp0, $rs_ecc_codewords);
        }
        fclose ($fp0);

        //--- set terminator
        if ($total_data_bits <= $max_data_bits - 4) {
            $data_value[$data_counter] = 0;
            $data_bits[$data_counter] = 4;
        } else {
            if ($total_data_bits < $max_data_bits) {
                $data_value[$data_counter] = 0;
                $data_bits[$data_counter] = $max_data_bits - $total_data_bits;
            } else {
                if ($total_data_bits > $max_data_bits) {
                    COM_errorLog('QRcode : Overflow error');
                    return false;
                }
            }
        }

        //--- divide data by 8bit
        $codewords = array();
        $codewords_counter = 0;
        $codewords[0] = 0;
        $remaining_bits = 8;

        for ($i = 0; $i <= $data_counter; $i++) {
            $buffer = @$data_value[$i];
            $buffer_bits = @$data_bits[$i];

            $flag = 1;
            while ($flag) {
                if ($remaining_bits > $buffer_bits) {
                    $codewords[$codewords_counter] = ((@$codewords[$codewords_counter]<<$buffer_bits) | $buffer);
                    $remaining_bits -= $buffer_bits;
                    $flag = 0;
                } else {
                    $buffer_bits -= $remaining_bits;
                    $codewords[$codewords_counter] = (($codewords[$codewords_counter] << $remaining_bits) | ($buffer >> $buffer_bits));

                    if ($buffer_bits == 0) {
                        $flag = 0;
                    } else {
                        $buffer = ($buffer & ((1 << $buffer_bits) - 1) );
                        $flag=1;
                    }

                    $codewords_counter++;
                    if ($codewords_counter < $max_data_codewords-1) {
                        $codewords[$codewords_counter] = 0;
                    }
                    $remaining_bits = 8;
                }
            }
        }

        if ($remaining_bits != 8) {
            $codewords[$codewords_counter] = $codewords[$codewords_counter] << $remaining_bits;
        } else {
            $codewords_counter--;
        }

        //--- set padding character
        if ($codewords_counter < $max_data_codewords - 1) {
            $flag = 1;
            while ($codewords_counter < $max_data_codewords - 1) {
                $codewords_counter++;
                if ($flag == 1) {
                    $codewords[$codewords_counter] = 236;
                } else {
                    $codewords[$codewords_counter] = 17;
                }
                $flag = $flag * (-1);
            }
        }

        //--- RS-ECC prepare
        $j = 0;
        $rs_block_number = 0;
        $rs_temp[0] = '';

        for ($i = 0, $j = 1; $i < $max_data_codewords; $i++, $j++) {
            $rs_temp[$rs_block_number] .= chr($codewords[$i]);

            if ($j >= $rs_block_order[$rs_block_number+1] - $rs_ecc_codewords) {
                $j = 0;
                $rs_block_number++;
                $rs_temp[$rs_block_number] = '';
            }
        }

        // RS-ECC main
        $rs_block_order_num = count($rs_block_order);

        for ($rs_block_number = 0; $rs_block_number < $rs_block_order_num;
                $rs_block_number++) {
            $rs_codewords = $rs_block_order[$rs_block_number + 1];
            $rs_data_codewords = $rs_codewords - $rs_ecc_codewords;

            $rstemp=$rs_temp[$rs_block_number].str_repeat(chr(0),$rs_ecc_codewords);
            $padding_data=str_repeat(chr(0),$rs_data_codewords);

            for ($j = $rs_data_codewords; $j > 0; $j--) {
                $first = ord(substr($rstemp, 0, 1));

                if ($first) {
                    $left_chr = substr($rstemp,1);
                    $cal = $rs_cal_table_array[$first] . $padding_data;
                    $rstemp = $left_chr ^ $cal;
                } else {
                    $rstemp=substr($rstemp,1);
                }
            }
            $codewords = array_merge($codewords, unpack('C*', $rstemp));
        }

        //--- flash matrix
        for ($i = 0; $i < $max_modules_1side; $i++) {
            for ($j = 0; $j < $max_modules_1side; $j++) {
                $matrix_content[$j][$i] = 0;
            }
        }

        //--- attach data
        for ($i = 0; $i < $max_codewords; $i++) {
            $codeword_i = $codewords[$i];
            for ($j = 8; $j > 0; $j--) {
                $codeword_bits_number = ($i << 3) +  $j;
                $matrix_content[$matrix_x_array[$codeword_bits_number]][ $matrix_y_array[$codeword_bits_number]] = ((255 * ($codeword_i & 1)) ^ $mask_array[$codeword_bits_number]);
                $codeword_i = $codeword_i >> 1;
            }
        }

        $matrix_remain = $matrix_remain_bit[$this->version];
        while ($matrix_remain) {
            $remain_bit_temp = $matrix_remain + ($max_codewords <<3);
            $matrix_content[$matrix_x_array[$remain_bit_temp]][$matrix_y_array[$remain_bit_temp]] = (255 ^ $mask_array[$remain_bit_temp]);
            $matrix_remain--;
        }

        //--- mask select
        $min_demerit_score = 0;
        $hor_master = '';
        $ver_master = '';
        for ($k = 0; $k < $max_modules_1side; $k++) {
            for ($l = 0; $l < $max_modules_1side; $l++) {
                $hor_master=$hor_master . chr($matrix_content[$l][$k]);
                $ver_master=$ver_master . chr($matrix_content[$k][$l]);
            }
        }

        $all_matrix = $max_modules_1side * $max_modules_1side;
        for ($i = 0; $i < 8; $i++) {
            $demerit_n1 = 0;
            $ptn_temp = array();
            $bit = 1 << $i;
            $bit_r = (~$bit) & 255;
            $bit_mask = str_repeat(chr($bit), $all_matrix);
            $hor = $hor_master & $bit_mask;
            $ver = $ver_master & $bit_mask;

            $ver_shift1 = $ver . str_repeat(chr(170), $max_modules_1side);
            $ver_shift2 = str_repeat(chr(170), $max_modules_1side) . $ver;
            $ver_shift1_0 = $ver . str_repeat(chr(0),$max_modules_1side);
            $ver_shift2_0 = str_repeat(chr(0),$max_modules_1side) . $ver;
            $ver_or = chunk_split(~($ver_shift1 | $ver_shift2),$max_modules_1side,chr(170));
            $ver_and = chunk_split(~($ver_shift1_0 & $ver_shift2_0),$max_modules_1side,chr(170));

            $hor = chunk_split(~$hor, $max_modules_1side, chr(170));
            $ver = chunk_split(~$ver, $max_modules_1side, chr(170));
            $hor = $hor . chr(170).$ver;

            $n1_search = "/" . str_repeat(chr(255),5) . "+|" . str_repeat(chr($bit_r),5) . "+/";
            $n3_search = chr($bit_r) . chr(255) . chr($bit_r) . chr($bit_r) . chr($bit_r) . chr(255) . chr($bit_r);

            $demerit_n3 = substr_count($hor, $n3_search) * 40;
            $demerit_n4 = floor(abs(( (100 * (substr_count($ver, chr($bit_r)) / ($byte_num)) ) - 50) / 5)) * 10;

            $n2_search1 = '/' . chr($bit_r) . chr($bit_r) . '+/';
            $n2_search2 = '/' . chr(255) . chr(255) . '+/';
            $demerit_n2 = 0;
            preg_match_all($n2_search1, $ver_and, $ptn_temp);
            foreach ($ptn_temp[0] as $str_temp) {
                $demerit_n2 += strlen($str_temp) - 1;
            }
            $ptn_temp=array();
            preg_match_all($n2_search2, $ver_or, $ptn_temp);
            foreach ($ptn_temp[0] as $str_temp) {
                $demerit_n2 += (strlen($str_temp) - 1);
            }
            $demerit_n2 *= 3;

            $ptn_temp=array();

            preg_match_all($n1_search, $hor, $ptn_temp);
            foreach ($ptn_temp[0] as $str_temp){
                $demerit_n1 += (strlen($str_temp) - 2);
            }

            $demerit_score = $demerit_n1 + $demerit_n2 + $demerit_n3 + $demerit_n4;
            if ($demerit_score <= $min_demerit_score || $i == 0) {
                $mask_number = $i;
                $min_demerit_score = $demerit_score;
            }
        }

        $mask_content = 1 << $mask_number;

        //--- format information
        $format_information_value = (($this->ec << 3) | $mask_number);
        $format_information_array = array(
            "101010000010010", "101000100100101",
            "101111001111100", "101101101001011",
            "100010111111001", "100000011001110",
            "100111110010111", "100101010100000",
            "111011111000100", "111001011110011",
            "111110110101010", "111100010011101",
            "110011000101111", "110001100011000",
            "110110001000001", "110100101110110",
            "001011010001001", "001001110111110",
            "001110011100111", "001100111010000",
            "000011101100010", "000001001010101",
            "000110100001100", "000100000111011",
            "011010101011111", "011000001101000",
            "011111100110001", "011101000000110",
            "010010010110100", "010000110000011",
            "010111011011010", "010101111101101",
        );

        for ($i = 0; $i < 15; $i++) {
            $content = substr($format_information_array[$format_information_value], $i, 1);
            $matrix_content[$format_information_x1[$i]][$format_information_y1[$i]] = $content * 255;
            $matrix_content[$format_information_x2[$i+1]][$format_information_y2[$i+1]] = $content * 255;
        }

        $mib = $max_modules_1side + 8;
        $qrcode_image_size = $mib * $this->module_size;
        if ($qrcode_image_size > 1480) {
            COM_errorLog('QRcode : Too large image size');
            return false;
        }
        $output_image = imagecreate($qrcode_image_size, $qrcode_image_size);
        $image_path = $image_path . '/qrv' . $this->version .'.png';
        $base_image = imagecreatefrompng($image_path);

        $col = array(
            0 => ImageColorAllocate($base_image,255,255,255),
            1 => ImageColorAllocate($base_image,0,0,0),
        );

        $mxe = 4 + $max_modules_1side;
        for ($i = 4, $ii = 0; $i < $mxe; $i++, $ii++) {
            for ($j = 4, $jj = 0; $j < $mxe; $j++, $jj++) {
                if ($matrix_content[$ii][$jj] & $mask_content) {
                    ImageSetPixel($base_image,$i,$j,$col[1]);
                }
            }
        }

        ImageCopyResized($output_image,$base_image,0,0,0,0,$qrcode_image_size,$qrcode_image_size,$mib,$mib);
        $filespec = $this->filepath . $this->filename;
        if ($this->image_type == 'jpg') {
            $this->have_image = imagejpeg($output_image, $filespec);
        } else {
            $this->have_image = imagepng($output_image, $filespec);
        }
        return $this->have_image;
    }

}

?>
