<?php

namespace Drupal\require_on_publish\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the RequireOnPublish constraint.
 */
class RequireOnPublishValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($field, Constraint $constraint) {
    $node = $field->getEntity();

    /*
     * @todo Right now, there isn't a great way to determine whether the field
     * is on a field config form OR the node edit form itself. This is
     * obviously a hack and should be fixed as soon as a better solution
     * presents itself.
     */
    if ($node->getTitle() === NULL) {
      return;
    }

    if ($node->isPublished() && $field->isEmpty()) {
      $label = $field->getFieldDefinition()->getLabel();
      $this->context->addViolation($constraint->message, ['%field_label' => $label]);
    }
  }

}
