<?php

namespace Drupal\require_on_publish\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks fields that are required on publish.
 *
 * Throws an error if a user tries to publish the entity WITHOUT filling in a
 * required field.
 *
 * @Constraint(
 *   id = "require_on_publish",
 *   label = @Translation("Require on Publish", context = "Validation")
 * )
 */
class RequireOnPublish extends Constraint {

  public $message = 'Field "%field_label" is required when publishing.';

}
