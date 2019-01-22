<?php

namespace Drupal\imageapi_optimize\Tests;

use Drupal\Tests\image\FunctionalJavascript\ImageFieldValidateTest as OriginalImageFieldValidateTest;

/**
 * Tests validation functions such as min/max resolution.
 *
 * @group image
 */
class ImageFieldValidateTest extends OriginalImageFieldValidateTest {

  public static $modules = ['imageapi_optimize'];

}
