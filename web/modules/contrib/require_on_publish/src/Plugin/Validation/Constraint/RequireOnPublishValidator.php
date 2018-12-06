<?php

namespace Drupal\require_on_publish\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\field\FieldConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the RequireOnPublish constraint.
 */
class RequireOnPublishValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a RequireOnPublishValidator object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('module_handler'));
  }

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity)) {
      return;
    }

    $is_published = $entity->isPublished();
    if ($this->moduleHandler->moduleExists('paragraphs')) {
      $paragraph_interface = '\Drupal\paragraphs\ParagraphInterface';
      if (($entity instanceof $paragraph_interface) && $entity->getParentEntity()) {
        if (require_on_publish_entity_is_publishable($entity->getParentEntity())) {
          $is_published = $entity->getParentEntity()->isPublished();
        }
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
