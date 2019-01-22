<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Functional\ImageDimensionsTest as OriginalImageDimensionsTest;

/**
 * Tests creation, deletion, and editing of image styles and effects.
 *
 * @group image
 */
class ImageDimensionsTest extends OriginalImageDimensionsTest {
  public static $modules = ['imageapi_optimize'];

}
