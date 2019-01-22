<?php

namespace Drupal\Tests\imageapi_optimize\Unit;

use Drupal\imageapi_optimize_binaries\Plugin\ImageAPIOptimizeProcessor\AdvPng;

/**
 * Tests AdvPng image optimize plugin.
 *
 * @group imageapi_optimize
 */
class AdvPngTest extends BinaryTestCase {

  /**
   * Test that the default config of the plugin is set.
   */
  public function testDefaultConfig() {
    $config = [];
    $loggerMock = $this->getLoggerMock();
    $imageFactoryMock = $this->getImageFactoryMock();
    $fileSystemMock = $this->getFileSystemMock();
    $shellOperationsMock = $this->getShellOperationsMock();

    $advpng = new AdvPng($config, 'advpng', [], $loggerMock, $imageFactoryMock, $fileSystemMock, $shellOperationsMock);

    $this->assertArrayHasKey('recompress', $advpng->defaultConfiguration());
    $this->assertArrayHasKey('mode', $advpng->defaultConfiguration());
  }

  /**
   * Test that the AdvPng plugin does not run on JPGs
   */
  public function testOnlyApplyToPNG() {
    $config = [];
    $loggerMock = $this->getLoggerMock();
    $imageFactoryMock = $this->getImageFactoryMock();
    $fileSystemMock = $this->getFileSystemMock();
    $shellOperationsMock = $this->getShellOperationsMock();

    $advpng = new AdvPng($config, 'advpng', [], $loggerMock, $imageFactoryMock, $fileSystemMock, $shellOperationsMock);

    $imagePNGMock = $this->getMock('\Drupal\Core\Image\ImageInterface');
    $imagePNGMock->method('getMimeType')->willReturn('image/jpeg');
    $imageFactoryMock->method('get')->willReturn($imagePNGMock);

    // Assert no call is made through to the shell commands.
    $shellOperationsMock->expects($this->never())->method('execShellCommand');

    // And assert that a 'jpg' isn't processed.
    $this->assertFalse($advpng->applyToImage('public://test_image.jpg'));
  }

  /**
   * @dataProvider advpngProvider
   */
  public function testPNGOptimized($config, $options) {
    $loggerMock = $this->getLoggerMock();
    $imageFactoryMock = $this->getImageFactoryMock();
    $fileSystemMock = $this->getFileSystemMock();
    $shellOperationsMock = $this->getShellOperationsMock();

    $advpng = new AdvPng(['data' => $config], 'advpng', [], $loggerMock, $imageFactoryMock, $fileSystemMock, $shellOperationsMock);

    $imagePNGMock = $this->getMock('\Drupal\Core\Image\ImageInterface');
    $imagePNGMock->method('getMimeType')->willReturn('image/png');
    $imageFactoryMock->method('get')->willReturn($imagePNGMock);

    $shellOperationsMock->expects($this->once())
      ->method('execShellCommand')
      ->with(
        $this->equalTo('advpng'),
        $this->identicalTo($options),
        $this->identicalTo(['public://test_image.png'])
      );

    $advpng->applyToImage('public://test_image.png');

  }

  /**
   * Provides config and the associated options that should be sent to advpng for thos options.
   *
   * @return array
   */
  public function advpngProvider() {
    $cases = [];

    $cases[] = [[], ['--quiet', '--recompress', '-3']];
    $cases[] = [['recompress' => FALSE], ['--quiet', '-3']];
    $cases[] = [['recompress' => FALSE, 'mode' => 1], ['--quiet', '-1']];
    $cases[] = [['mode' => 0], ['--quiet', '--recompress', '-0']];
    $cases[] = [['mode' => 4], ['--quiet', '--recompress', '-4']];

    return $cases;
  }
}
