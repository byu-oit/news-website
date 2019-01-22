<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Functional\ImageOnTranslatedEntityTest as OriginalImageOnTranslatedEntityTest;

/**
 * Tests creation, deletion, and editing of image styles and effects.
 *
 * @group image
 */
class ImageOnTranslatedEntityTest extends OriginalImageOnTranslatedEntityTest {
  public static $modules = ['imageapi_optimize'];

}
