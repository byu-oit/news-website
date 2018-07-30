<?php

namespace Drupal\field_token_value\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides the Field Token Value field type.
 *
 * @FieldType(
 *   id = "field_token_value",
 *   module = "field_token_value",
 *   label = @Translation("Field Token Value"),
 *   description = @Translation("Create a field value with the use of tokens."),
 *   default_widget = "field_token_value_default",
 *   default_formatter = "field_token_value_text"
 * )
 */
class FieldTokenValueItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'field_value' => '',
      'remove_empty' => TRUE,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Value'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'text',
          'size' => 'big',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['field_value'] = [
      '#type' => 'textfield',
      '#maxlength' => 1024,
      '#title' => $this->t('Field value'),
      '#description' => $this->t('Enter the value for this field. Tokens are automatically replaced upon saving of the node itself.'),
      '#default_value' => $this->getSetting('field_value'),
      '#element_validate' => array('token_element_validate'),
      '#token_types' => array($this->getEntity()->getEntityTypeId()),
      '#required' => TRUE,
    ];

    $element['token_help'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [$this->getEntity()->getEntityTypeId()],
    ];

    $element['remove_empty'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove empty tokens'),
      '#description' => $this->t('Select this option to remove tokens from the final text if no replacement value can be generated.'),
      '#default_value' => $this->getSetting('remove_empty'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    $entity = $this->getEntity();
    $entity_type = $entity->getEntityTypeId();

    // Replace the tokens and save as the field value.
    $token = \Drupal::token();
    $this->value = $token->replace($this->value,
      [$entity_type => $entity],
      ['clear' => $this->getSetting('remove_empty')]
    );
  }

}
