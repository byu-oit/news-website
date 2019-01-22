<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Functional\ImageStylesPathAndUrlTest as OriginalImageStylesPathAndUrlTest;

/**
 * Tests creation, deletion, and editing of image styles and effects.
 *
 * @group image
 */
class ImageStylesPathAndUrlTest extends OriginalImageStylesPathAndUrlTest {
  public static $modules = ['imageapi_optimize'];

}
