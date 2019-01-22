<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Functional\ImageFieldDefaultImagesTest as OriginalImageFieldDefaultImagesTest;

/**
 * Tests creation, deletion, and editing of image styles and effects.
 *
 * @group image
 */
class ImageFieldDefaultImagesTest extends OriginalImageFieldDefaultImagesTest {
  public static $modules = ['imageapi_optimize'];

}
