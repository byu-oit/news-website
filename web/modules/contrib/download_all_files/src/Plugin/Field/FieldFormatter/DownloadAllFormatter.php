<?php

namespace Drupal\download_all_files\Plugin\Field\FieldFormatter;

use Drupal\file\Plugin\Field\FieldFormatter\TableFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'file_download_all' formatter.
 *
 * @FieldFormatter(
 *   id = "file_download_all",
 *   label = @Translation("Table of files with download all link"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class DownloadAllFormatter extends TableFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    if (!empty($elements)) {
      $field_name = $items->getName();
      $parent_node_id = $items->getParent()->get('nid')->getValue()[0]['value'];
      $url = Url::fromUserInput('/download_all_files/' . $parent_node_id . '/' . $field_name);
      $download_all_files_link = Link::fromTextAndUrl('Download All', $url)->toRenderable();
      $download_all_files_link['#attributes']['class'] = array('download-all-files');
      $elements[]['download_all_files'] = $download_all_files_link;
    }

    return $elements;
  }

}
