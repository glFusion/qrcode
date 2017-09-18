# qrcode for glFusion
The qrcode plugin for glFusion creates QR Code images for display in blocks,
articles, pages, or by other plugins.

## Autotag
Basic Syntax: [qrcode:*data_string*]

Options:
* *data_string* - The data to be encoded. You can also specify:
  * *home* or *top* for the home page URL
  * *current* for the current page URL
  * *text* for any other string. The data string must follow any other parmeters
* *s* or *size*:value to override the image size
* *t* or *type*:value to override the image type. Default is PNG
* *e* or *ecc*:value to override the ECC value

The defaults for size, type and ECC are set in the plugin configuration.

## Service Function
Plugins may request a QRCode image by calling LGLIB_invokeService which will
call the QRCode plugin's service function, if available.

Usage: $status = LGLIB_invokeService('qrcode', array('data'=>'data to encode'), $output, $svc_msg);

The second argument must be an array with an element named "data" containing the data to encode. Spaces are allowed in this case.

Additional array elements can be provided to override default values:
* module_size: Set the image size
* image_type = "png" or "jpg"
* ecc_level = Error Correction Level:
  * Level L – up to 7% damage
  * Level M – up to 15% damage
  * Level Q – up to 25% damage
  * Level H – up to 30% damage

The resulting $output variable is populated with:
```
array(
  'img'  => Image filename only,
  'path' => Full filesystem path to the image,
  'html' => Full "img src" tag, including default sizing and styling,
  'url'  => Image URL only to be styled and rendered by the caller,
);
```
Based on the Geeklog plugin of the same name by Yoshinori Tahara
* copyright  Copyright (c) 2010-2016 Lee Garner <lee@leegarner.com>
* copyright  2010 Yoshinori Tahara - dengen - taharaxp AT gmail DOT com

"QR Code" is a registred trademark of DENSO WAVE INCORPORATED (www.qrcode.com).
