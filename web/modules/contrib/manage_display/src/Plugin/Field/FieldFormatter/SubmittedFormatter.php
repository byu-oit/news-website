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
    $elements = parent::viewElements($items, $langcode);

    foreach ($elements as $delta => $entity) {
      $elements[$delta]['#theme'] = 'submitted';
    }

    return $elements;
  }

}
