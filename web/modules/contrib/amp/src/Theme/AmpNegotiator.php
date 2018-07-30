<?php

namespace Drupal\amp\Theme;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\amp\Routing\AmpContext;

/**
 * Sets the active theme on amp pages.
 */
class AmpNegotiator extends ServiceProviderBase implements ThemeNegotiatorInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AmpContext.
   *
   * @var \Drupal\amp\Routing\AmpContext
   */
  protected $ampContext;

  /**
   * Creates a new AmpNegotiator instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\amp\Routing\AmpContext $ampContext
   *   The AmpContext.
   */
  public function __construct(ConfigFactoryInterface $configFactory, AmpContext $ampContext) {
    $this->configFactory = $configFactory;
    $this->ampContext = $ampContext;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $routeMatch) {
    // See if this route and object are AMP, without checking the active theme.
    $is_amp_route = $this->ampContext->isAmpRoute($routeMatch, NULL, FALSE);
    if ($is_amp_route) {
      // Disable big pipe on AMP pages.
      // @todo Rely on https://www.drupal.org/node/2729441 instead, when it is
      //   resolved.
      $routeMatch->getRouteObject()->setOption('_no_big_pipe', TRUE);
    }
    return $is_amp_route;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return $this->configFactory->get('amp.theme')->get('amptheme');
  }

}
