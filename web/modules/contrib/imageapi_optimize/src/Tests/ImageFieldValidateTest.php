<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\Functional\ImageFieldValidateTest as OriginalImageFieldValidateTest;

/**
 * Tests validation functions such as min/max resolution.
 *
 * @group image
 */
class ImageFieldValidateTest extends OriginalImageFieldValidateTest {

  public static $modules = ['node', 'image', 'field_ui', 'image_module_test', 'imageapi_optimize'];

}
