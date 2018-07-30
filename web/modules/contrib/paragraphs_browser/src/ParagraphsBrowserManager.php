<?php
/**
 * @file
 */

namespace Drupal\paragraphs_browser;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * BrowserManager class.
 */
class BrowserManager {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface;
   */
  protected $entityTypeManager;
  /**
   * Constructs a BrowserManager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }
}