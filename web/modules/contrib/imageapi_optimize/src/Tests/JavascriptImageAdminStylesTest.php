<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\FunctionalJavascript\ImageAdminStylesTest as OriginalImageAdminStylesTest;

/**
 * Tests creation, deletion, and editing of image styles and effects.
 *
 * @group image
 */
class ImageAdminStylesTest extends OriginalImageAdminStylesTest {
  public static $modules = ['imageapi_optimize'];

}
