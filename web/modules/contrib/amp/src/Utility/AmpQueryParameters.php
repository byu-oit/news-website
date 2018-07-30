<?php

namespace Drupal\amp\Utility;

use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class AmpQueryParameters
 *
 * Adds amp query parameters to a URL.
 *
 * @package Drupal\amp\Utility
 */
class AmpQueryParameters extends ServiceProviderBase {

  /**
   * Add amp query parameter to a URL.
   *
   * @param string $url
   *   The original URL value.
   * @param boolean $warnfix
   *   Option to append warnfix to the end of the URL.
   *
   * @return string
   *   A url containing the additional amp query parameter(s).
   */
  public function add($url, $warnfix = FALSE) {
    // Append amp query string parameter
    if (strpos($url, '?') === FALSE) {
      $amp_url = $url . "?amp";
    }
    else {
      $amp_url = $url . "&amp";
    }

    // Append optional warnfix query string parameter.
    if ($warnfix) {
      $amp_url = $amp_url . "&warnfix";
    }

    return $amp_url;
  }
}
