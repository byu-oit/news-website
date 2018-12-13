<?php

namespace Drupal\Tests\require_on_publish\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the 'Require on Publish' functionality on field config pages.
 *
 * @group require_on_publish
 */
class ConfigPageTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field_ui',
    'require_on_publish',
    'taxonomy',
  ];

  /**
   * The account to login as.
   *
   * @var \Drupal\user\Entity\UserInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create the 'article' content type.
    /** @var \Drupal\node\NodeTypeInterface $type */
    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'type' => 'article',
        'name' => 'Article',
      ]);
    $type->save();
    $this->container->get('router.builder')->rebuild();

    // Create the 'field_tags' field.
    FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_tags',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'taxonomy_term',
      ],
    ])->save();

    // Add 'field_tags' to the 'article' content type.
    FieldConfig::create([
      'field_name' => 'field_tags',
      'entity_type' => 'node',
      'bundle' => 'article',
    ])->save();

    // Create a user who can administer fields.
    $this->account = $this->drupalCreateUser(['administer node fields']);
  }

  /**
   * Logs in and navigates to a field config edit page.
   */
  protected function logIn() {
    $this->drupalLogin($this->account);
    $this->drupalGet('admin/structure/types/manage/article/fields/node.article.field_tags');
  }

  /**
   * Test that the 'Required on Publish' field exists.
   */
  public function testRequireOnPublishExists() {
    $this->logIn();

    // Test that there is a checkbox for 'Required on Publish'.
    $this->assertSession()->fieldExists('require_on_publish');
  }

  /**
   * Test that the 'Required on Publish' field toggle works.
   *
   * 'Required on Publish' and 'Required' should not be able to both be checked
   * at the same time. This test ensures that the Javascript that enforces that
   * is working.
   */
  public function testRequireOnPublishToggles() {
    $this->logIn();

    $this->getSession()->getPage()->fillField('require_on_publish', 1);
    // Verify 'Required' is empty.
    $required = $this->getSession()->getPage()->findField('required')->getValue();
    $this->assertEqual($required, 0);

    // Click 'Required'.
    $this->getSession()->getPage()->fillField('required', 1);
    // Verify 'Required on Publish' is empty.
    $required = $this->getSession()->getPage()->findField('require_on_publish')->getValue();
    $this->assertEqual($required, 0);
  }

  /**
   * Test that the 'Required on Publish' field can be succesfully enabled.
   */
  public function testRequireOnPublishEnable() {
    $this->logIn();

    // Set 'Required on Publish'.
    $this->getSession()->getPage()->fillField('require_on_publish', 1);
    // Save.
    $this->getSession()->getPage()->pressButton('Save settings');
    // Ensure 'Required on Publish' is true.
    $required = $this->getSession()->getPage()->findField('require_on_publish')->getValue();
    $this->assertEqual($required, 1);
  }

  /**
   * Test that the 'Required on Publish' field can be succesfully disabled.
   */
  public function testRequireOnPublishDisable() {
    $this->logIn();

    // Unset 'Required on Publish'.
    $this->getSession()->getPage()->fillField('require_on_publish', 0);
    // Save the form.
    $this->getSession()->getPage()->pressButton('Save settings');
    // Ensure that 'Required on Publish' is false.
    $required = $this->getSession()->getPage()->findField('require_on_publish')->getValue();
    $this->assertEqual($required, 0);
  }

}
