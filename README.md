# qrcode for glFusion
The qrcode plugin for glFusion creates QR Code or barcode images for
display in blocks, articles, pages, or by other plugins.

## Autotag
Basic Syntax: `[qrcode:_type_ data_string]`

Options:
* _type_
  * *home* or *top* for the home page URL
  * *current* for the current page URL
  * *barcode* to create a barcode instead of a QR code
  * *text* for any other string. The data string must follow any other parmeters
* *s* or *size*:value to override the image size
* *t* or *type*:value to override the image type. Default is PNG
* *e* or *ecc*:value to override the ECC value
* *data_string* - The data to be encoded. You can also specify:

The defaults for size, type and ECC are set in the plugin configuration.

### Examples
```
[qrcode:text s=5 http://www.myothersite.com]
[qrcode:home]
[qrcode:current]
[qrcode:current size=5 t=jpg e=L]
[qrcode:barcode 12345678]
```

## Service Function
Plugins may request a QRCode image by calling `LGLIB_invokeService` which will
call the QRCode plugin's service function, if available.

Usage: $status = `LGLIB_invokeService('qrcode', array('data'=>'data to encode'), $output, $svc_msg);`

The second argument must be an array with an element named "data" containing the data to encode. Spaces are allowed in this case.

Additional array elements can be provided to override default values:
* `module_size`: Set the image size
* `image_type` = "png" or "jpg"
* `ecc_level` = Error Correction Level:
  * Level L – up to 7% damage
  * Level M – up to 15% damage
  * Level Q – up to 25% damage
  * Level H – up to 30% damage
* `type`: Set to `barcode` to return a barcode, otherwise a QR Code will be returned

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
* copyright  Copyright (c) 2010-2020 Lee Garner <lee@leegarner.com>
* copyright  2010 Yoshinori Tahara - dengen - taharaxp AT gmail DOT com
* Barcode funtion copyright 2003 David S. Tufts

"QR Code" is a registred trademark of DENSO WAVE INCORPORATED (www.qrcode.com).
