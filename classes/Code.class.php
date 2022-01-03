<?php
/**
 * Base Class to handle creating codes.
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
class Code
{
    /** Target filepath to save images.
     * @var string */
    protected $filepath = '';

    /** Image filename to save.
     * @var string */
    protected $filename = '';

    /** Data to be encoded.
     * @var string */
    protected $data = '';

    /** Indicate this image has been created.
     * Saves a call to file_exists if getURL, getPath, etc. are called in
     * the same session.
     * @var boolean */
    protected $have_image = false;


    /**
     * Constructor. Instantiate a qrCode object based on suppleid parameters.
     * The params array must contain at least a 'data' element with the
     * data to be encoded.
     *
     * @param   array   $params     Array of parameters for qrCode creation
     */
    public function __construct($params)
    {
        $this->filepath = Config::get('img_path');
    }


    /**
     * Get the image filename.
     * Creates the image if not already done.
     *
     * @return  string  Image filename (not full path)
     */
    public function getImage()
    {
        if ($this->have_image || $this->createImage()) {
            return $this->filename;
        } else {
            return NULL;
        }
    }


    /**
     * Get the full path to a QRCode image file.
     * Creates the image if not already done.
     *
     * @param   string  $filename   Image filename
     * @return  string          Full path to the image file
     */
    public function getPath()
    {
        if ($this->have_image || $this->createImage()) {
            return $this->filepath . $this->filename;
        } else {
            return '';
        }
    }


    /**
     * Get the URL to a QRCode image. Creates the image if not already done.
     * This returns only the image URL, leaving it up to to the caller
     * to create the complete image tag.
     *
     * @see     qrCode::getHTML()
     * @param   string  $filename   Image filename
     * @return  string      URL to render the image
     */
    function getURL()
    {
        if ($this->have_image || $this->createImage()) {
            return Config::get('url') . '/img.php?img=' . $this->filename;
        } else {
            return '';
        }
    }


    /**
     * Determine if an image file exists.
     *
     * @param   string  $filename   Filename only of image
     * @return  boolean     True if the file exists, False if not
     */
    public static function exists($filename)
    {
        return file_exists(Config::get('img_path') . $filename);
    }


    /**
     * Check if the current image file exists.
     *
     * @uses    self::exists()
     * @return  boolean     True if file is on disk, False if not
     */
    public function file_exists()
    {
        return self::exists($this->filename);
    }


    /**
     * Get the mime-type value depending on the image type used.
     *
     * @param   string  $type   Type of image, JPG or PNG
     * @return  string          Mime type corresponding to image type
     */
    public static function MimeType()
    {
        switch (Config::get('image_type')) {
        case 'jpg':
            return 'image/jpeg';
            break;
        case 'png':
        default:
            return 'image/png';
            break;
        }
    }


    /**
    *   Clean out old QR code images.
    *
    *   @return boolean     True on success, False on failure or if not needed
    */
    public static function cleanCache()
    {
        if (Config::get('cache_clean_interval') < 0) {
            // No cache cleaning required
            return false;
        }
        $lastCleanFile = Config::get('img_path') . 'qrc_cacheLastCleanTime.touch';

        //If this is a new timthumb installation we need to create the file
        if (!is_file($lastCleanFile)) {
            if (!touch($lastCleanFile)) {
                COM_errorLog("QRCODE: Cannot touch cache clean file $lastCleanFile");
            }
            return false;
        }

        $cache_clean_interval = Config::get('cache_clean_interval') * 60;  // minutes
        $cache_max_age = Config::get('cache_max_age') * 86400; // days
        if (@filemtime($lastCleanFile) < (time() - $cache_clean_interval)) {
            //Cache was last cleaned more than FILE_CACHE_TIME_BETWEEN_CLEANS ago
            if (!touch($lastCleanFile)) {
                COM_errorLog(__METHOD__ . ': Could not create cache clean timestamp file.');
                return false;
            }
            $files = glob(Config::get('img_path') . '*.' . Config::get('image_type'));
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

}

