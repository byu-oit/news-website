<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Drupal\Core\ImageToolkit\ImageToolkitOperationBase;

/**
 * Class ImagickOperationBase
 *
 * @package Drupal\imagick\Plugin\ImageToolkit\Operation\imagick
 */
abstract class ImagickOperationBase extends ImageToolkitOperationBase {

  use ImagickOperationTrait;

  /**
   * The correctly typed image toolkit for GD operations.
   *
   * @return \Drupal\imagick\Plugin\ImageToolkit\ImagickToolkit
   */
  protected function getToolkit() {
    return parent::getToolkit();
  }

}
