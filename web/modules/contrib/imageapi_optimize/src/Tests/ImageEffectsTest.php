<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Functional\ImageEffectsTest as OriginalImageEffectsTest;

/**
 * Tests creation, deletion, and editing of image styles and effects.
 *
 * @group image
 */
class ImageEffectsTest extends OriginalImageEffectsTest {
  public static $modules = ['imageapi_optimize'];

}
