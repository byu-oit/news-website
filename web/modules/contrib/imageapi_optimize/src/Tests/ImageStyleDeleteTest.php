<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Functional\ImageStyleDeleteTest as OriginalImageStyleDeleteTest;

/**
 * Tests image style deletion using the UI.
 *
 * @group image
 */
class ImageStyleDeleteTest extends OriginalImageStyleDeleteTest {

  public static $modules = ['node', 'image', 'field_ui', 'image_module_test', 'imageapi_optimize'];

}
