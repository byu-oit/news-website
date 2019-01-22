<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Functional\ImageFieldDisplayTest as OriginalImageFieldDisplayTest;

/**
 * Tests creation, deletion, and editing of image styles and effects.
 *
 * @group image
 */
class ImageFieldDisplayTest extends OriginalImageFieldDisplayTest {
  public static $modules = ['imageapi_optimize'];

}
