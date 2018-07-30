<?php

namespace Drupal\amp;

use Drupal\Core\Url;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Service class for retrieving and manipulating entity type information.
 */
class EntityTypeInfo extends ServiceProviderBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cache backend to use for the complete theme registry data.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constructs a new EntityTypeRepository.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend interface to use for the complete theme registry data.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CacheBackendInterface $cache) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cache = $cache;
  }

  /**
   * Checks if AMP is enabled for this bundle.
   *
   * @param string $node_type
   *   The node type to check whether AMP is enabled or not.
   *
   * @return bool
   *   TRUE if AMP is enabled for this bundle. FALSE otherwise.
   */
  public function isAmpEnabledType($node_type) {
    $amp_display = $this->entityTypeManager
      ->getStorage('entity_view_display')
      ->load('node.' . $node_type . '.amp');

    if ($amp_display && $amp_display->status()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns a list of AMP-enabled content types.
   *
   * @return array
   *   An array of bundles that have AMP view modes enabled.
   */
  public function getAmpEnabledTypes() {
    $enabled_types = [];
    if ($cache = $this->cache->get('amp_enabled_types')) {
      $enabled_types = $cache->data;
    }
    else {
      // Get the list of node entities with AMP view mode enabled and store
      // their bundles.
      $ids = $this->entityTypeManager
        ->getStorage('entity_view_display')
        ->getQuery()
        ->condition('id', 'node.', 'STARTS_WITH')
        ->condition('mode', 'amp')
        ->condition('status', TRUE)
        ->execute();
      if ($ids) {
        foreach ($ids as $id) {
          $parts = explode('.', $id);
          $enabled_types[$parts[1]] = $parts[1];
        }
      }
      $this->cache->set('amp_enabled_types', $enabled_types);
    }
    return $enabled_types;
  }

  /**
   * Returns a formatted list of AMP-enabled content types.
   *
   * @return array
   *   A list of content types that provides the following:
   *     - Each content type enabled on the site.
   *     - The enabled/disabled status for each content type.
   *     - A link to enable/disable view modes for each content type.
   *     - A link to configure the AMP view mode, if enabled.
   */
  public function getFormattedAmpEnabledTypes() {
    $enabled_types = !empty($this->getAmpEnabledTypes()) ? $this->getAmpEnabledTypes() : array();
    $node_types = node_type_get_names();
    $node_status_list = array();
    $destination = Url::fromRoute("amp.settings")->toString();
    foreach ($node_types as $bundle => $label) {
      $configure = Url::fromRoute("entity.entity_view_display.node.view_mode", ['node_type' => $bundle, 'view_mode_name' => 'amp'], ['query' => ['destination' => $destination]])->toString();
      $enable_disable = Url::fromRoute("entity.entity_view_display.node.default", ['node_type' => $bundle], ['query' => ['destination' => $destination]])->toString();
      if (in_array($bundle, $enabled_types)) {
        $node_status_list[] = t('@label is <em>enabled</em>: <a href=":configure">Configure AMP view mode</a> or <a href=":enable_disable">Disable AMP display</a>', array(
            '@label' => $label,
            ':configure' => $configure,
            ':enable_disable' => $enable_disable,
          ));
      }
      else {
        $node_status_list[] = t('@label is <em>disabled</em>: <a href=":enable_disable">Enable AMP in Custom Display Settings</a>', array(
            '@label' => $label,
            ':enable_disable' => $enable_disable,
          ));
      }
    }
    return $node_status_list;
  }
}
