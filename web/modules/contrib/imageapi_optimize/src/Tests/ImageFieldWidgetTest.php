<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Functional\ImageFieldWidgetTest as OriginalImageFieldWidgetTest;

/**
 * Tests creation, deletion, and editing of image styles and effects.
 *
 * @group image
 */
class ImageFieldWidgetTest extends OriginalImageFieldWidgetTest {
  public static $modules = ['imageapi_optimize'];

}
