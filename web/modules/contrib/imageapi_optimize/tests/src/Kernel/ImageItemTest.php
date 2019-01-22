<?php

namespace Drupal\Tests\imageapi_optimize\Kernel;

use Drupal\Tests\image\Kernel\ImageItemTest as OriginalImageItemTest;

/**
 * Tests using entity fields of the image field type.
 *
 * @group image
 */
class ImageItemTest extends OriginalImageItemTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['file', 'image', 'imageapi_optimize'];

}
