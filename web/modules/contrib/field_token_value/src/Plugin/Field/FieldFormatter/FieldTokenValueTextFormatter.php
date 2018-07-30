<?php

namespace Drupal\field_token_value\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\field_token_value\WrapperManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Field Token Value Text field formatter.
 *
 * @FieldFormatter(
 *   id = "field_token_value_text",
 *   label = @Translation("Text"),
 *   field_types = {
 *     "field_token_value"
 *   }
 * )
 */
class FieldTokenValueTextFormatter extends FormatterBase implements ContainerFactoryPluginInterface{

  /**
   * The field token value wrapper manager.
   *
   * @var \Drupal\field_token_value\WrapperManagerInterface
   */
  protected $wrappers;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('field_token_value.wrapper_manager')
    );
  }

  /**
   * Constructs a DateTimeTimeAgoFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\field_token_value\WrapperManagerInterface $wrappers
   *   The field token value wrapper service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, WrapperManagerInterface $wrappers) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->wrappers = $wrappers;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return ['wrapper' => ''] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['wrapper'] = [
      '#type' => 'select',
      '#title' => t('Wrapper'),
      '#description' => t('The wrapper to use for the field output.'),
      '#default_value' => $this->getSetting('wrapper'),
      '#options' => $this->wrappers->getWrapperOptions(),
      '#empty_option' => t('- Select wrapper -'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $selected = $this->getSetting('wrapper');

    if (!empty($selected)) {
      $wrapper = $this->wrappers->getDefinition($selected);
      $summary[] = $this->t('Display: @summary', ['@summary' => $wrapper['summary']]);
    }
    else {
      $summary[] = $this->t('No wrapper has been selected so a paragraph tag will be used by default');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $selected = $this->getSetting('wrapper');

    // Because the field value is determined by the instance settings, even if the
    // user somehow managed to add multiple items, the same value will be set for
    // each one. Because of this we only ever use the first value.
    if (!empty($items[0])) {
      $element[0] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $items[0]->value,
      ];

      if (!empty($selected)) {
        // Retrieve the wrapper info from the service.
        $wrapper_info = $this->wrappers->getDefinition($selected);

        // Update the output tag based on the wrapper info.
        $element[0]['#tag'] = $wrapper_info['tag'];

        // If the wrapper contains attributes such as class, add them in.
        if (isset($wrapper_info['attributes'])) {
          $element[0]['#attributes'] = $wrapper_info['attributes'];
        }

        // Allow modules to alter the output of the field. For example to possibly
        // attach CSS or JS for a particular tag.
        \Drupal::moduleHandler()->alter('field_token_value_output', $element[0], $wrapper_info);
      }
    }

    return $element;
  }

}
