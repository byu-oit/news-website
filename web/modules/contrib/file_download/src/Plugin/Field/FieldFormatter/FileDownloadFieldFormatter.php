<?php

namespace Drupal\file_download\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\EntityInterface;
use Drupal\views\Plugin\views\PluginBase;


/**
 *
 * @FieldFormatter(
 *   id = "file_download_formatter",
 *   label = @Translation("File Download"),
 *   field_types = {
 *     "file",
 *     "image"
 *   }
 * )
 */
class FileDownloadFieldFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $options = parent::defaultSettings();
    $options['link_title'] = 'file';
    $options['custom_title_text'] = '';
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['link_title'] = [
      '#type' => 'radios',
      '#options' => $this->getDisplayOptions(),
      '#title' => $this->t('Title Display'),
      '#description' => $this->t('Control what is displayed in the title of the link'),
      '#default_value' => $this->getSetting('link_title'),
    ];

    $fieldName = $this->fieldDefinition->getName();
    $form['custom_title_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom text'),
      '#default_value' => $this->getSetting('custom_title_text'),
      '#placeholder' => $this->t('e.g. "Download"'),
      '#description' => $this->t('Provide a custom text to display for all download links.  This field takes HTML and @link', array('@link' => '<a href="/admin/help/token">file entity tokens for current user, file and parent entity.</a>')),
      '#states' => [
        'visible' => [
          ":input[name=\"fields[{$fieldName}][settings_edit_form][settings][link_title]\"]" => ['value' => 'custom'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * @return array
   */
  private function getDisplayOptions() {
    return [
      'file' => $this->t('Title of file'),
      'entity_title' => $this->t('Title of parent entity'),
      'description' => $this->t('Contents of the description field'),
      'empty' => $this->t('Nothing'),
      'custom' => $this->t('Custom text')
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = [];
    $settings = $this->getSettings();
    $displayOptions = $this->getDisplayOptions();

    $selectedTitleDisplay = $settings['link_title'];
    $tArgs = ['@view' => $displayOptions[$selectedTitleDisplay]];
    $summary[] = $this->t('Title Display: @view', $tArgs);

    if ($selectedTitleDisplay === 'custom') {
      $tArgs = ['@text' => $settings['custom_title_text']];
      $summary[] = $this->t('Custom text: @text', $tArgs);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $token_data = [
      'user' => \Drupal::currentUser(),
      $items->getEntity()->getEntityTypeId() => $items->getEntity(),
    ];
    $settings = $this->getSettings();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $token_data['file'] = $file;

      $item = $file->_referringItem;

      switch ($settings['link_title']) {

        // This is useful for instance if you are using an icon
        case 'empty':
          $title = '';
          break;

        case 'entity_title':
          $entity = $items->getEntity();
          if ($entity->get('title')->getValue() != NULL) {
            $title = $entity->get('title')->getValue()[0]['value'];
          }
          break;

        case 'custom':
          $title = \Drupal::token()->replace($settings['custom_title_text'], $token_data, ['clear' => TRUE]);
          break;

        case 'description':
          $title = $item->description;
          break;

        // This equates to choosing filename
        default:
          // If title has no value then filename is substituted
          // See template_preprocess_download_file_link()
          $title = NULL;
      }

      $elements[$delta] = [
        '#theme' => 'download_file_link',
        '#file' => $file,
        '#title' => $title,
        '#description' => $item->description,
        '#cache' => [
          'tags' => $file->getCacheTags(),
        ],
      ];

      // Pass field item attributes to the theme function.
      if (isset($item->_attributes)) {
        $elements[$delta] += ['#attributes' => []];
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }

    }

    return $elements;
  }

}
