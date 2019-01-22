<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Kernel\ImageThemeFunctionTest as OriginalImageThemeFunctionTest;

/**
 * Tests creation, deletion, and editing of image styles and effects.
 *
 * @group image
 */
class ImageThemeFunctionTest extends OriginalImageThemeFunctionTest {
  public static $modules = ['imageapi_optimize'];

}
