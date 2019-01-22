<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Functional\ImageStyleFlushTest as OriginalImageStyleFlushTest;

/**
 * Tests creation, deletion, and editing of image styles and effects.
 *
 * @group image
 */
class ImageStyleFlushTest extends OriginalImageStyleFlushTest {
  public static $modules = ['imageapi_optimize'];

}
