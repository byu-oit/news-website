<?php

namespace Drupal\block_token;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockTokenPermissions implements ContainerInjectionInterface {

//  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'));
  }

  public static function permissions() {
    $perms = [];

    $perms['administer block token'] = array(
      'title' => t('Administer block tokens'),
      'description' => t('Turn on/off the block token generation per block.(This gives permission to View/Edit/Save block forms.)'),
    );

    return $perms;
  }
}
