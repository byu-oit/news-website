<?php

namespace Drupal\amp\Service;

use Lullabot\AMP\AMP;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class AMPService.
 *
 * @package Drupal\amp
 */
class AMPService extends ServiceProviderBase  {
  // amp-analytics maps to the amp/amp.amp.analytics library (and so forth) but it could be anything arbitrary in the future
  // This is why we're being extremely explicit. We're not going to employ any tricks to convert amp-xyz to amp/amp.amp.xyz
  protected $library_names = [
      'amp-ad' => 'amp/amp.ad',
      'amp-access' => 'amp/amp.access',
      'amp-accordion' => 'amp/amp.accordion',
      'amp-analytics' => 'amp/amp.analytics',
      'amp-anim' => 'amp/amp.anim',
      'amp-audio' => 'amp/amp.audio',
      'amp-brid-player' => 'amp/amp.brid-player',
      'amp-brightcove' => 'amp/amp.brightcove',
      'amp-carousel' => 'amp/amp.carousel',
      'amp-dailymotion' => 'amp/amp.dailymotion',
      'amp-dynamic-css-classes' => 'amp/amp.dynamic-css-classes',
      'amp-embed' => 'amp/amp.embed',
      'amp-facebook' => 'amp/amp.facebook',
      'amp-fit-text' => 'amp/amp.fit-text',
      'amp-font' => 'amp/amp.font',
      'amp-iframe' => 'amp/amp.iframe',
      'amp-instagram' => 'amp/amp.instagram',
      'amp-install-serviceworker' => 'amp/amp.install-serviceworker',
      'amp-image-lightbox' => 'amp/amp.image-lightbox',
      'amp-jwplayer' => 'amp/amp.jwplayer',
      'amp-kaltura-player' => 'amp/amp.kaltura-player',
      'amp-lightbox' => 'amp/amp.lightbox',
      'amp-list' => 'amp/amp.list',
      'amp-pinterest' => 'amp/amp.pinterest',
      'amp-pixel' => 'amp/amp.pixel',
      'amp-sidebar' => 'amp/amp.sidebar',
      'amp-slides' => 'amp/amp.slides',
      'amp-social-share' => 'amp/amp.social-share',
      'amp-soundcloud' => 'amp/amp.soundcloud',
      'amp-springboard-player' => 'amp/amp.springboard-player',
      'amp-sticky-ad' => 'amp/amp.sticky-ad',
      'amp-twitter' => 'amp/amp.twitter',
      'amp-user-notification' => 'amp/amp.user-notification',
      'amp-video' => 'amp/amp.video',
      'amp-vine' => 'amp/amp.vine',
      'amp-vimeo' => 'amp/amp.vimeo',
      'amp-youtube' => 'amp/amp.youtube',
      'template' => 'amp/amp.template', // exception to the above pattern
  ];

  /**
   * This is your starting point.
   * Its cheap to create AMP objects now.
   * Just create a new one every time you're asked for it.
   *
   * @return AMP
   */
  public function createAMPConverter() {
    return new AMP();
  }

  /**
   * Given an array of components e.g. amp-iframe, make an array of library
   */
  public function addComponentLibraries(array $components) {
    $library_paths = [];
    /**
     * @var string $component_name
     * @var string $component_url We dont need this for now
     */
    foreach($components as $component_name => $component_url) {
      if (isset($this->library_names[$component_name])) {
        $library_paths[] = $this->library_names[$component_name];
      }
    }
    return $library_paths;
  }
}
