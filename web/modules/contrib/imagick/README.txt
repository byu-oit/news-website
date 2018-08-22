CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended Modules
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

The Imagick module provides an image toolkit implementation based on
ImageMagick. Unlike the ImageMagick module, which invokes convert binary to
process images, this module uses the Imagick PHP extension. It allows custom
effects that need to access image data such as Smart Crop.

 * For a full description of the module visit:
   https://www.drupal.org/project/imagick

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/imagick


REQUIREMENTS
------------

This module requires:

 *  Imagick PHP extension - http://php.net/manual/en/book.imagick.php


RECOMMENDED MODULES
-------------------

It allows custom effects that need to access image data such as Smart Crop.

 * Smart Crop - https://www.drupal.org/project/smartcrop


INSTALLATION
------------

Install the Imagick module as you would normally install a contributed Drupal
module. Visit https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

    1. Navigate to Administration » Configuration » Media » Image toolkit -
       change the image toolkit selection to "Imagick".
    2. ImageMagick effects will now be available to apply to Image Styles at
       Administration » Configuration » Media » Image styles in the "EFFECT"
       table.
    3. Optional: Enable optimization to comply with Google PageSpeed guidelines.


MAINTAINERS
-----------

 * Brecht Ceyssens (bceyssens) - https://www.drupal.org/u/bceyssens
 * Bram Goffings (aspilicious) - https://www.drupal.org/u/aspilicious

Supporting organization:

* Nascom - https://www.drupal.org/nascom
