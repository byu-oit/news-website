<?php

namespace Drupal\manage_display\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\user\Plugin\Field\FieldFormatter\AuthorFormatter;

/**
 * A field formatter for entity titles.
 *
 * @FieldFormatter(
 *   id = "submitted",
 *   label = @Translation("Submitted"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class SubmittedFormatter extends AuthorFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    //@todo call parent then switch theme?

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      /** @var $referenced_user \Drupal\user\UserInterface */
      $elements[$delta] = [
        '#theme' => 'submitted',
        '#account' => $entity,
        '#link_options' => ['attributes' => ['rel' => 'author']],
        '#cache' => [
          'tags' => $entity->getCacheTags(),
        ],
      ];
    }

    return $elements;
  }

}
