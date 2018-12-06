<?php

namespace Drupal\Tests\require_on_publish\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests Require on Publish functionality on the entity edit page.
 *
 * @group require_on_publish
 */
class EditPageTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'datetime',
    'datetime_range',
    'field_ui',
    'file',
    'image',
    'layout_builder',
    'link',
    'node',
    'options',
    'path',
    'require_on_publish',
    'taxonomy',
    'telephone',
    'text',
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

    $bundle = 'article';

    /** @var \Drupal\node\NodeTypeInterface $type */
    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'type' => $bundle,
        'name' => 'Article',
      ]);
    $type->save();

    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $entity_form */
    $entity_form = $this->container->get('entity_type.manager')
      ->getStorage('entity_form_display')
      ->create([
        'targetEntityType' => 'node',
        'bundle' => $bundle,
        'mode' => 'default',
        'status' => TRUE,
      ]);

    $node_base_field_definitions = $this->container->get('entity.manager')
      ->getBaseFieldDefinitions('node');

    $blacklisted_field_types = [
      'file_uri',
      'entity_reference',
      'language',
    ];
    $field_type_plugin_manager = $this->container->get('plugin.manager.field.field_type');
    foreach ($this->container->get('plugin.manager.field.widget')->getDefinitions() as $widget) {
      foreach ($widget['field_types'] as $field_type) {
        if (in_array($field_type, $blacklisted_field_types)) {
          continue;
        }
        if (in_array($field_type, array_keys($node_base_field_definitions))) {
          continue;
        }

        $field_name = $widget['id'] . '__' . $field_type;
        $field_id = substr($field_name, 0, 32);

        FieldStorageConfig::create([
          'entity_type' => 'node',
          'field_name' => $field_id,
          'type' => $field_type,
          'settings' => $field_type_plugin_manager->getDefaultStorageSettings($field_type),
        ])->save();

        $field_config = FieldConfig::create([
          'field_name' => $field_id,
          'label' => $field_name,
          'entity_type' => 'node',
          'bundle' => $bundle,
          'third_party_settings' => [
            'require_on_publish' => [
              'require_on_publish' => TRUE,
            ],
          ],
        ])->save();
        $entity_form->setComponent($field_id, ['type' => $widget['id']])->save();
      }
    }

    drupal_flush_all_caches();
    $this->account = $this->drupalCreateUser(['create article content']);
  }

  /**
   * Ensure there is an indicator for the 'Required on Publish' fields.
   */
  public function testIndicators() {
    $this->drupalLogin($this->account);
    $this->drupalGet('node/add/article');

    $page = $this->getSession()->getPage();

    $entity_form = \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load('node.article.default');
    $node_base_field_definitions = $this->container->get('entity.manager')
      ->getBaseFieldDefinitions('node');

    foreach ($entity_form->getComponents() as $field_name => $component) {
      if (in_array($field_name, array_keys($node_base_field_definitions))) {
        continue;
      }

      $field_id = sprintf("edit-%s-wrapper", str_replace('_', '-', $field_name));
      $field_id = str_replace('--', '-', $field_id);

      $field = $page->find('css', "#$field_id");
      if ($field && $field->isVisible()) {
        $html = $field->getOuterHtml();
        $indicator_exists = strpos($html, 'form-required-on-publish') !== FALSE;
        $message = sprintf('(%s:%s): %s', $field_name, $field_id, $html);
        $this->assertTrue($indicator_exists, $message);
      }
    }
  }

}
