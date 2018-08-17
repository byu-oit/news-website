<?php

namespace Drupal\Tests\asset_injector\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Class AssetInjectorCssTest.
 *
 * @package Drupal\Tests\asset_injector\Functional
 *
 * @group asset_injector
 */
class AssetInjectorCssTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['asset_injector', 'toolbar', 'block'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->container->get('theme_installer')->install(['bartik', 'seven']);
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('page_title_block');
  }

  /**
   * Tests a user without permissions gets access denied.
   */
  public function testCssPermissionDenied() {
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);
    $this->drupalGet('admin/config/development/asset-injector/css');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests a user WITH permission has access.
   */
  public function testCssPermissionGranted() {
    $account = $this->drupalCreateUser(['administer css assets injector']);
    $this->drupalLogin($account);
    $this->drupalGet('admin/config/development/asset-injector/css');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test a created css injector is added to the page and the css file exists.
   *
   * @throws \Exception
   */
  public function testCssInjector() {
    $this->testCssPermissionGranted();
    $this->drupalGet('admin/config/development/asset-injector/css/add');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains(t('Code'));
    $this->submitForm([
      'label' => t('Blocks'),
      'id' => t('blocks'),
      'code' => '.block {border:1px solid black;}',
    ], t('Save'));

    $this->getSession()->getPage()->hasContent('asset_injector/css/blocks');
    /** @var \Drupal\asset_injector\Entity\AssetInjectorCss $asset */
    foreach (asset_injector_get_assets(NULL, ['asset_injector_css']) as $asset) {
      $path = parse_url(file_create_url($asset->internalFileUri()), PHP_URL_PATH);
      $path = str_replace(base_path(), '/', $path);

      $this->drupalGet($path);
      $this->assertSession()->statusCodeEquals(200);
    }
  }

}
