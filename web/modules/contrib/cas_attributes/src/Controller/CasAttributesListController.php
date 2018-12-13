<?php

namespace Drupal\cas_attributes\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class CasAttributesListController.
 */
class CasAttributesListController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Used to get query string parameters from the request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * A messenger object.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Settings object for CAS attributes.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $settings;


  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Symfony request stack.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(RequestStack $request_stack, MessengerInterface $messenger, ConfigFactoryInterface $config_factory) {
    $this->requestStack = $request_stack;
    $this->messenger = $messenger;
    $this->settings = $config_factory->get('cas_attributes.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('request_stack'), $container->get('messenger'), $container->get('config.factory'));
  }

  /**
   * Lists all currently loaded CAS attributes.
   */
  public function content() {
    $build = [
      '#cache' => [
        'contexts' => [
          'session',
        ],
      ],
    ];

    $session = $this->requestStack
      ->getCurrentRequest()
      ->getSession();

    if (!$this->settings->get('sitewide_token_support')) {
      $this->messenger->addError($this->t('You must <a href="@link">enable sitewide token support</a> to view the list of available attributes for the currently logged in user. Note that enabling that feature is not required to define user field mappings, but it is required if you want to use this page.', ['@link' => Url::fromRoute('cas_attributes.settings')->toString()]));
    }
    else {
      if (!$session->get('is_cas_user')) {
        $this->messenger->addError($this->t('You must login through CAS view available CAS attributes.'));
      }
      else {
        $attributes = $this->requestStack
          ->getCurrentRequest()
          ->getSession()
          ->get('cas_attributes', []);

        $table = [
          '#type' => 'table',
          '#header' => ['Name', 'Token', 'Value'],
          '#empty' => $this->t('There are no CAS attributes associated with your session.'),
          '#caption' => $this->t('This table contains all attributes returned from your CAS server for the currently logged in user.'),
        ];

        $row = 0;
        foreach ($attributes as $attrName => $attrValue) {
          $table[$row] = [
            'name' => ['#plain_text' => $attrName],
            'token' => ['#plain_text' => '[cas:attribute:' . mb_strtolower($attrName . ']')],
            'value' => ['#plain_text' => var_export($attrValue, TRUE)],
          ];
          $row++;
        }

        $build['table'] = $table;
      }
    }

    return $build;
  }

}
