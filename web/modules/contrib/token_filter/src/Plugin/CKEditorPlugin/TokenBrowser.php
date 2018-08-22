<?php

namespace Drupal\token_filter\Plugin\CKEditorPlugin;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "tokenbrowser" plugin.
 *
 * NOTE: The plugin ID ('id' key) corresponds to the CKEditor plugin name.
 * It is the first argument of the CKEDITOR.plugins.add() function in the
 * plugin.js file.
 *
 * @CKEditorPlugin(
 *   id = "tokenbrowser",
 *   label = @Translation("Token browser")
 * )
 */
class TokenBrowser extends CKEditorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The CSRF token manager service.
   *
   * @var Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfTokenService;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('csrf_token')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param Drupal\Core\Access\CsrfTokenGenerator $csrf_token_service
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CsrfTokenGenerator $csrf_token_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->csrfTokenService = $csrf_token_service;
  }

  /**
   * {@inheritdoc}
   *
   * NOTE: The keys of the returned array corresponds to the CKEditor button
   * names. They are the first argument of the editor.ui.addButton() or
   * editor.ui.addRichCombo() functions in the plugin.js file.
   */
  public function getButtons() {
    return [
      'tokenbrowser' => [
        'id' => 'tokenbrowser',
        'label' => t('Token browser'),
        'image' => file_create_url($this->getImage()),
        'link' => $this->getUrl()->toString(),
      ],
    ];
  }

  /**
   * Fetches the URL.
   *
   * @return Drupal\Core\Url
   *   The URL.
   *
   * @see TokenTreeController::outputTree().
   */
  protected function getUrl() {
    $url = Url::fromRoute('token.tree');
    $options['query'] = [
      'options' => Json::encode($this->getQueryOptions()),
      'token' => $this->csrfTokenService->get($url->getInternalPath()),
    ];
    $url->setOptions($options);
    return $url;
  }

  /**
   * Fetches the list of query options.
   *
   * @return array
   *   The list of query options.
   *
   * @see TreeBuilderInterface::buildRenderable() for option definitions.
   */
  protected function getQueryOptions() {
    return [
      'token_types' => 'all',
      'global_types' => FALSE,
      'click_insert' => TRUE,
      'show_restricted' => FALSE,
      'show_nested' => FALSE,
      'recursion_limit' => 3,
    ];
  }

  /**
   * Fetches the path to the image.
   *
   * Make sure that the path to the image matches the file structure of the
   * CKEditor plugin you are implementing.
   *
   * @return string
   *   The string representation of the path to the image.
   */
  protected function getImage() {
    return $this->getModulePath() . '/js/plugins/tokenbrowser/tokenbrowser.png';
  }

  /**
   * {@inheritdoc}
   *
   * Make sure that the path to the plugin.js matches the file structure of the
   * CKEditor plugin you are implementing.
   */
  public function getFile() {
    return $this->getModulePath() . '/js/plugins/tokenbrowser/plugin.js';
  }

  /**
   * Fetches the path to this module.
   *
   * @return string
   *   The string representation of the module's path.
   */
  protected function getModulePath() {
    return drupal_get_path('module', 'token_filter');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'TokenBrowser_buttons' => $this->getButtons(),
    ];
  }

}
