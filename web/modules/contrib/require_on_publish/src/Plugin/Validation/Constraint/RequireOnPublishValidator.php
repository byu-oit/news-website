<?php

namespace Drupal\require_on_publish\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\field\FieldConfigInterface;

/**
 * Validates the RequireOnPublish constraint.
 */
class RequireOnPublishValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity)) {
      return;
    }

    $is_published = $entity->isPublished();
    if (\Drupal::service('module_handler')->moduleExists('paragraphs')) {
      $paragraph_interface = '\Drupal\paragraphs\ParagraphInterface';
      if (($entity instanceof $paragraph_interface) && $entity->getParentEntity()) {
        $is_published = $entity->getParentEntity()->isPublished();
      }
    }

    if ($is_published) {
      foreach ($entity->getFields() as $field) {
        $field_config = $field->getFieldDefinition();
        if (!($field_config instanceof FieldConfigInterface)) {
          continue;
        }
        if ($field_config->getThirdPartySetting('require_on_publish', 'require_on_publish', FALSE) && $field->isEmpty()) {
          $label = $field_config->getLabel();
          $this->context->addViolation($constraint->message, ['%field_label' => $label]);
        }
      }
    }
  }

}
