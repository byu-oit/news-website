<?php

namespace Drupal\Tests\node\Functional;

use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Tests setDisplayConfigurable.
 *
 * @group node
 * @see \Drupal\node\Controller\NodeController
 */
class NodeDisplayConfigurableTest extends NodeTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['quickedit', 'rdf'];

  /**
   * Tests the html head links.
   */
  public function testDisplayConfigurable() {
    // Change the node type setting to show submitted by information.
    $node_type = \Drupal::entityManager()->getStorage('node_type')->load('page');
    $node_type->setDisplaySubmitted(TRUE);
    $node_type->save();

    $user = $this->drupalCreateUser(['access in-place editing', 'administer nodes']);
    $this->drupalLogin($user);
    $node = $this->drupalCreateNode(['uid' => $user->id()]);
    $assert = $this->assertSession();

    // Check the node with Drupal default non-configurable display.
    $this->drupalGet($node->urlInfo());
    $assert->elementTextContains('css', 'span.field--name-created', format_date($node->getCreatedTime()));
    $assert->elementTextContains('css', 'span.field--name-uid[data-quickedit-field-id="node/1/uid/en/full"]', $user->getUsername());
    $assert->elementTextContains('css', 'div.node__submitted', 'Submitted by');
    $assert->elementTextContains('css', 'span.field--name-title', $node->getTitle());

    // Enable module to setDisplayConfigurable.
    \Drupal::service('module_installer')->install(['node_display_configurable_test']);

    // Configure display.
    $display = EntityViewDisplay::load('node.page.default');
    $display->setComponent('uid',
      [
        'type' => 'entity_reference_label',
        'label' => 'above',
        'settings' => ['link' => FALSE],
      ])
      ->save();

    // Recheck the node with configurable display.
    $this->drupalGet($node->urlInfo());
    $assert->elementTextContains('css', 'div.field--name-created', format_date($node->getCreatedTime()));
    $assert->elementTextContains('css', 'div.field--name-uid[data-quickedit-field-id="node/1/uid/en/full"] .field__item[rel="schema:author"]', $user->getUsername());
    $assert->elementNotExists('css', 'div.field--name-uid a');
    $assert->elementTextContains('css', 'div.field--name-uid div.field__label', 'Authored by');
    $assert->elementTextContains('css', 'span.field--name-title', $node->getTitle());
    $assert->elementExists('css', 'span[property="schema:dateCreated"]');

    // Remove from display.
    $display->removeComponent('uid')
      ->removeComponent('created')
      ->save();

    $this->drupalGet($node->urlInfo());
    $assert->elementNotExists('css', '.field--name-created');
    $assert->elementNotExists('css', '.field--name-uid');
  }

}
