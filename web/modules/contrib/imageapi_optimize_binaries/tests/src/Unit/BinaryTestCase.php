<?php

namespace Drupal\Tests\imageapi_optimize\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Base test for our binary tests
 *
 * @group imageapi_optimize
 */
abstract class BinaryTestCase extends UnitTestCase {

  protected function getLoggerMock() {
    return $this->getMock('\Psr\Log\LoggerInterface');
  }


  protected function getImageFactoryMock() {
    return $this->getMockBuilder('\Drupal\Core\Image\ImageFactory')
      ->disableOriginalConstructor()
      ->getMock();
  }

  protected function getFileSystemMock() {
    $fileSystemMock = $this->getMock('\Drupal\Core\File\FileSystemInterface');
    $fileSystemMock
      ->method('realpath')->will($this->returnArgument(0));
    return $fileSystemMock;
  }

  protected function getShellOperationsMock() {
    $shellOperationsMock =  $this->getMockBuilder('\Drupal\imageapi_optimize_binaries\ImageAPIOptimizeShellOperationsInterface')
      ->setMethods(['findExecutablePath', 'execShellCommand', 'saveCommandStdoutToFile'])
      ->getMock();

    $shellOperationsMock
      ->method('findExecutablePath')->will($this->returnArgument(0));

    return $shellOperationsMock;
  }
}
