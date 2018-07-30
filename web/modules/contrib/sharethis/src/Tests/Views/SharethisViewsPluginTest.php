<?php

namespace Drupal\sharethis\Tests\Views;

use Drupal\views\Views;
use Drupal\views\Tests\ViewTestData;
use Drupal\views\Tests\ViewTestBase;

/**
 * Tests the sharethis links appearing on node.
 *
 * @group sharethis
 *
 * @see \Drupal\sharethis\Plugin\views\field\SharethisNode.
 */
class SharethisViewsPluginTest extends ViewTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node', 'system_test', 'views', 'user', 'sharethis', 'sharethis_test_views',
  );

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = array('test_sharethis');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create and login user.
    $this->privileged_user = $this->drupalCreateUser(array('administer site configuration', 'access administration pages'));
    $this->drupalLogin($this->privileged_user);
    ViewTestData::createTestViews(get_class($this), array('sharethis_test_views'));
  }

  /**
   * Tests the handlers.
   */
  public function testHandlers() {
    $this->drupalCreateNode();
    $this->drupalCreateNode();
    // Test the sharethis field.
    $view = Views::getView('test_sharethis');
    $view->setDisplay('page_1');
    $this->executeView($view);
    $this->assertEqual(count($view->result), 2);
    $output = $view->preview();
    $this->setRawContent(\Drupal::service('renderer')->renderRoot($output));
    $result = $this->xpath('//div[@class=:class]', array(':class' => 'sharethis-wrapper'));
    $this->assertEqual(count($result), 2, 'Sharethis links found');
  }

}
