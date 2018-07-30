<?php

namespace Drupal\manage_display\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test the title field is configurable.
 *
 * @group title
 */
class NodeTeaserAndPage extends WebTestBase {

  public static $modules = ['node', 'manage_display', 'views'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $web_user = $this->drupalCreateUser([
      'administer nodes',
      'access content overview',
      'bypass node access',
      'access content',
      'administer content types',
    ]);
    $this->drupalLogin($web_user);
    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
      'display_submitted' => FALSE,
    ]);
  }

  /**
   * Test the title replacements work as expected.
   */
  public function testNodeTeaserAndPage() {
    $this->drupalPostForm('admin/structure/types/manage/page', ['display_configurable_title' => '1'], 'Save content type');

    $edit = [];
    $edit['title[0][value]'] = 'Test Content';
    $edit['body[0][value]'] = $this->randomMachineName(16);
    $this->drupalPostForm('node/add/page', $edit, t('Save and publish'));

    $this->assertRaw('<h1 class="title replaced-title" id="page-title">Test Content</h1>');

    $this->drupalGet('node');
    $this->assertRaw('<h2><a href="/node/1">Test Content</a></h2>');
  }
}
