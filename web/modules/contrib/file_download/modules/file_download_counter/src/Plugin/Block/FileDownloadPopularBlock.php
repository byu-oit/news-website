<?php

namespace Drupal\file_download_counter\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'Popular content' block.
 *
 * @Block(
 *   id = "statistics_popular_block",
 *   admin_label = @Translation("Popular content")
 * )
 */
class FileDownloadPopularBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'top_day_num' => 0,
      'top_all_num' => 0,
      'top_last_num' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Popular content block settings.
    $numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25, 30, 40];
    $numbers = ['0' => $this->t('Disabled')] + array_combine($numbers, $numbers);
    $form['file_download_counter_block_top_day_num'] = [
      '#type' => 'select',
      '#title' => $this->t("Number of day's top downloads to display"),
      '#default_value' => $this->configuration['top_day_num'],
      '#options' => $numbers,
      '#description' => $this->t('How many content items to display in "day" list.'),
    ];
    $form['file_download_counter_block_top_all_num'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of all time downloads to display'),
      '#default_value' => $this->configuration['top_all_num'],
      '#options' => $numbers,
      '#description' => $this->t('How many content items to display in "all time" list.'),
    ];
    $form['file_download_counter_block_top_last_num'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of most recent downloads to display'),
      '#default_value' => $this->configuration['top_last_num'],
      '#options' => $numbers,
      '#description' => $this->t('How many content items to display in "recently downloaded" list.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['top_day_num'] = $form_state->getValue('file_download_counter_block_top_day_num');
    $this->configuration['top_all_num'] = $form_state->getValue('file_download_counter_block_top_all_num');
    $this->configuration['top_last_num'] = $form_state->getValue('file_download_counter_block_top_last_num');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = [];

    if ($this->configuration['top_day_num'] > 0) {
      $result = file_download_counter_title_list('daycount', $this->configuration['top_day_num']);
      if ($result) {
        $content['top_day'] = node_title_list($result, $this->t("Today's:"));
        $content['top_day']['#suffix'] = '<br />';
      }
    }

    if ($this->configuration['top_all_num'] > 0) {
      $result = file_download_counter_title_list('totalcount', $this->configuration['top_all_num']);
      if ($result) {
        $content['top_all'] = node_title_list($result, $this->t('All time:'));
        $content['top_all']['#suffix'] = '<br />';
      }
    }

    if ($this->configuration['top_last_num'] > 0) {
      $result = file_download_counter_title_list('timestamp', $this->configuration['top_last_num']);
      $content['top_last'] = node_title_list($result, $this->t('Last viewed:'));
      $content['top_last']['#suffix'] = '<br />';
    }
    $content['#cache'] = [
      'max-age' => 0,
    ];
    return $content;
  }

}
