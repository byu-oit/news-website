<?php

namespace Drupal\Tests\amp\Functional;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\amp\Functional\AmpTestBase;

/**
 * Tests AMP view mode.
 *
 * @group amp
 */
class AmpViewModeTest extends AmpTestBase {


  /**
   * Test the AMP view mode.
   */
  public function testAmpViewMode() {

    // Create a node to test AMP field formatters.
    $node = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
      'body' => 'AMP test body',
    ]);
    $node->save();

    // Create an AMP context object.
    $amp_context = \Drupal::service('router.amp_context');

    // Check that the AMP view mode is available.
    $view_modes_url = Url::fromRoute('entity.entity_view_mode.collection')->toString();
    $this->drupalGet($view_modes_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('AMP');

    // Check the metadata of the full display mode.
    $node_url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString();
    $amp_node_url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE, 'query' => ['amp' => NULL]])->toString();
    $this->drupalGet($node_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('AMP test body');
    $this->assertSession()->responseContains('data-quickedit-field-id="node/1/body/en/full"');
    $this->assertSession()->responseContains('link rel="amphtml" href="' . $amp_node_url . '"');
    $this->assertSession()->responseHeaderEquals('Link', '<' . $amp_node_url . '> rel="amphtml"');

    // Check the metadata of the AMP display mode.
    $this->drupalGet($amp_node_url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('AMP test body');
    $this->assertSession()->responseContains('data-quickedit-field-id="node/1/body/en/amp"');
    $this->assertSession()->responseContains('link rel="canonical" href="' . $node_url . '"');

    // Test the warnfix parameter.
    $this->drupalGet($amp_node_url . "&warnfix");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('AMP test body');
    $this->assertSession()->pageTextContains('AMP-HTML Validation Issues and Fixes');
    $this->assertSession()->pageTextContains('-------------------------------------');
    $this->assertSession()->pageTextContains('PASS');

  }
}
