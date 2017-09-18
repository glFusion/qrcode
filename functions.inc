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
*   @version    1.0.2
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

require_once __DIR__ . '/qrcode.php';

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

spl_autoload_register(function($class)
{
    if (strpos($class, 'qrCode\\') === 0) {
        $class = str_replace('qrCode\\', '', $class);
        $path = __DIR__ . '/classes/' . $class . '.class.php';
        if (file_exists($path)) {
            include $path;
        }
    }
});


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
    require_once __DIR__ . '/autoinstall.php';
    return QRC_autouninstall();
}


/**
*   Upgrade the QRCode plugin.
*/
function plugin_upgrade_qrcode()
{
    require_once QRC_PI_PATH . '/upgrade.inc.php';
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
        $params = array(
            'module_size' => 3,
            'ecc_level' => 'M',
            'image_type' => 'png',
        );

        switch ($autotag['parm1']) {
            case 'home':
            case 'top':
                $params['data'] = $_CONF['site_url'];
                break;
            case 'current':
                $params['data'] = COM_buildURL(COM_getCurrentURL());
                break;
            case 'text':
            default:
                $params['data'] = '';
                    //COM_applyFilter($autotag['parm1']);
                break;
        }

        // These parameters have default values in QRC_qrcode if not defined
        if (!empty($autotag['parm2'])) {
            $parm2 = htmlentities($autotag['parm2'], NULL, 'utf-8');
            $parm2 = str_replace(array('&nbsp;', '&amp;nbsp;'), ' ', $parm2);
            $parm2 = explode(' ', $parm2);
            $skip = 0;
            foreach ($parm2 as $p) {
                list($var,$val) = explode('=', $p);
                switch ($var) {
                case 's':
                case 'size':
                    $params['module_size'] = $val;
                    $skip++;
                    break;
                case 't':
                case 'type':
                    $params['image_type'] = $val;
                    $skip++;
                    break;
                case 'e':
                case 'ecc':
                    $params['ecc_level'] = strtoupper($val);
                    $skip++;
                    break;
                default:
                    // nothing to do
                    break;
                }
            }

            // Any leftover parts become the caption.  Borrowed from
            // Mediagallery's caption handling.
            if ($skip > 0) {
                if (count($parm2) > $skip) {
                    for ($i = 0; $i < $skip; $i++) {
                        // Skip to the end of the processed directives
                        array_shift($parm2);
                    }
                    $caption = trim(implode(' ', $parm2));
                } else {
                    $caption = '';
                }
            } else {
                // There weren't any control parameters, so all of parm2 is
                // the caption.
                $caption = trim($autotag['parm2']);
            }
            $params['data'] = $caption;
        }

        $block = '';
        if (!empty($params['data'])) {
            $Q = new qrCode\qrCode($params);
            $block = $Q->getHTML($params);
        }
        $content = str_replace($autotag['tagstr'], $block, $content);
        return $content;
    }
}


/**
*   Get the URL to the configuration help file.
*
*   @param  string  $option     Config option selected
*   @param  string  $doclang    Document language
*   @return string              URL to document
*/
function plugin_getconfigelementhelp_qrcode($option, $doclang='english')
{
    global $_QRC_CONF;

    if (!function_exists('LGLIB_getDocuRL'))
        return false;

    $doc_url = LGLIB_getDocURL('config.html', $_QRC_CONF['pi_name'], $doclang);
    if (empty($doc_url))
        return false;

    if (!empty($option)) {
        $file .= '#desc_' . $option;
    }

    return array($doc_url . $file, 0);
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
    $Q = new qrCode\qrCode($params);
    return $Q->getHTML(array('phpblock'));
}


/**
*   Get additional CSS into the site header
*
*   @return array   List of paths to CSS files
*/
function plugin_getheadercss_qrcode()
{
    global $_CONF, $_QRC_CONF;

    $styles = array();
    $files = array(
        'style.css',
    );

    // When looking for CSS files, we'll check in this order:
    //  1. layout/custom
    //  2. layout
    //  3. plugin/custom
    //  4. plugin (default distribution version)
    $pi_css = QRC_PI_PATH . '/css/';
    $layout_css = "{$_CONF['path_layout']}plugins/{$_QRC_CONF['pi_name']}/";

    foreach ($files as $file) {
        if (@file_exists($layout_css . 'custom/' . $file)) {
            $styles[] = $layout_css . 'custom/' . $file;
        } elseif (@file_exists($layout_css . $file)) {
            $styles[] = $layout_css . $file;
        } elseif (@file_exists($pi_css . 'custom/' . $file)) {
            $styles[] = $pi_css . 'custom/' . $file;
        } else {
            $styles[] = $pi_css . $file;
        }
    }
    return $styles;
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
    global $_QRC_CONF;

    if (!is_array($args)) return PLG_RET_ERROR;
    if (!isset($args['data'])) return PLG_RET_ERROR;
    $output = array();
    $Q = new qrCode\qrCode($args);
    $output['img'] = $Q->getImage();
    if (!$output['img']) return PLG_RET_ERROR;

    $output['path'] = $Q->getPath();
    $output['html'] = $Q->getHTML();
    $output['url']  = $Q->getURL();
    $output['type'] = $_QRC_CONF['image_type'];
    return PLG_RET_OK;
}

?>
