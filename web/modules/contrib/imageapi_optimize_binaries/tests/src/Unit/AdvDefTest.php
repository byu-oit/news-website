<?php

namespace Drupal\Tests\imageapi_optimize\Unit;

use Drupal\imageapi_optimize_binaries\Plugin\ImageAPIOptimizeProcessor\AdvDef;

/**
 * Tests AdvDef image optimize plugin.
 *
 * @group imageapi_optimize
 */
class AdvDefTest extends BinaryTestCase {

  /**
   * Test that the default config of the plugin is set.
   */
  public function testDefaultConfig() {
    $config = [];
    $loggerMock = $this->getLoggerMock();
    $imageFactoryMock = $this->getImageFactoryMock();
    $fileSystemMock = $this->getFileSystemMock();
    $shellOperationsMock = $this->getShellOperationsMock();

    $advdef = new AdvDef($config, 'advdef', [], $loggerMock, $imageFactoryMock, $fileSystemMock, $shellOperationsMock);

    $this->assertArrayHasKey('recompress', $advdef->defaultConfiguration());
    $this->assertArrayHasKey('mode', $advdef->defaultConfiguration());
  }

  /**
   * Test that the AdvDef plugin does not run on JPGs
   */
  public function testOnlyApplyToPNG() {
    $config = [];
    $loggerMock = $this->getLoggerMock();
    $imageFactoryMock = $this->getImageFactoryMock();
    $fileSystemMock = $this->getFileSystemMock();
    $shellOperationsMock = $this->getShellOperationsMock();

    $advdef = new AdvDef($config, 'advdef', [], $loggerMock, $imageFactoryMock, $fileSystemMock, $shellOperationsMock);

    $imagePNGMock = $this->getMock('\Drupal\Core\Image\ImageInterface');
    $imagePNGMock->method('getMimeType')->willReturn('image/jpeg');
    $imageFactoryMock->method('get')->willReturn($imagePNGMock);

    // Assert no call is made through to the shell commands.
    $shellOperationsMock->expects($this->never())->method('execShellCommand');

    // And assert that a 'jpg' isn't processed.
    $this->assertFalse($advdef->applyToImage('public://test_image.jpg'));
  }

  /**
   * @dataProvider advdefProvider
   */
  public function testPNGOptimized($config, $options) {
    $loggerMock = $this->getLoggerMock();
    $imageFactoryMock = $this->getImageFactoryMock();
    $fileSystemMock = $this->getFileSystemMock();
    $shellOperationsMock = $this->getShellOperationsMock();

    $advdef = new AdvDef(['data' => $config], 'advdef', [], $loggerMock, $imageFactoryMock, $fileSystemMock, $shellOperationsMock);

    $imagePNGMock = $this->getMock('\Drupal\Core\Image\ImageInterface');
    $imagePNGMock->method('getMimeType')->willReturn('image/png');
    $imageFactoryMock->method('get')->willReturn($imagePNGMock);

    $shellOperationsMock->expects($this->once())
      ->method('execShellCommand')
      ->with(
        $this->equalTo('advdef'),
        $this->identicalTo($options),
        $this->identicalTo(['public://test_image.png'])
      );

    $advdef->applyToImage('public://test_image.png');

  }

  /**
   * Provides config and the associated options that should be sent to advdef for thos options.
   *
   * @return array
   */
  public function advdefProvider() {
    $cases = [];

    $cases[] = [[], ['--quiet', '--recompress', '-3']];
    $cases[] = [['recompress' => FALSE], ['--quiet', '-3']];
    $cases[] = [['recompress' => FALSE, 'mode' => 1], ['--quiet', '-1']];
    $cases[] = [['mode' => 1], ['--quiet', '--recompress', '-1']];
    $cases[] = [['mode' => 9], ['--quiet', '--recompress', '-9']];

    return $cases;
  }
}
