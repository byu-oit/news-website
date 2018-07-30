<?php

namespace Drupal\manage_display\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\BasicStringFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Template\Attribute;

/**
 * A field formatter for entity titles.
 *
 * @FieldFormatter(
 *   id = "title",
 *   label = @Translation("Title"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class TitleFormatter extends BasicStringFormatter {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $parent = $items->getParent()->getValue();
    foreach ($items as $item) {
      $text = $item->getValue()['value'];
      if ($this->getSetting('linked')) {
        $text = $this->l($text, $parent->toUrl());
      }
      $output[] = [
        '#type' => 'html_tag',
        '#tag' => $this->getSetting('tag'),
        '#value' => $text,
      ];
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $heading_options = [
      'span' => 'span',
      'div' => 'div',
    ];
    foreach (range(1, 5) as $level) {
      $heading_options['h' . $level] = 'H' . $level;
    }
    $form['tag'] = [
      '#title' => $this->t('Tag'),
      '#type' => 'select',
      '#description' => $this->t('Select the tag which will be wrapped around the title.'),
      '#options' => $heading_options,
      '#default_value' => $this->getSetting('tag'),
    ];
    $form['linked'] = [
      '#title' => $this->t('Link to the Content'),
      '#type' => 'checkbox',
      '#description' => $this->t('Wrap the title with a link to the content.'),
      '#default_value' => $this->getSetting('linked'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'tag' => 'h2',
      'linked' => '1',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $replacements = ['@tag' => $settings['tag']];
    if ($settings['linked']) {
      $summary[] = $this->t('Display as @tag, linked to content', $replacements);
    }
    else {
      $summary[] = $this->t('Display as @tag', $replacements);
    }

    return $summary;
  }

}
