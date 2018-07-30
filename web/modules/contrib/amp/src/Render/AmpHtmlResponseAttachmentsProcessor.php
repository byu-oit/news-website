<?php

namespace Drupal\amp\Render;

use Drupal\Core\Render\HtmlResponseAttachmentsProcessor;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\amp\Routing\AmpContext;
use Drupal\Core\Asset\AttachedAssetsInterface;

/**
 * Processes attachments of AMP HTML responses.
 *
 * This class is used by the rendering service to process the #attached part of
 * the render array, for AMP HTML responses.
 *
 * To render attachments to HTML for testing without a controller, use the
 * 'bare_html_page_renderer' service to generate a
 * Drupal\Core\Render\HtmlResponse object. Then use its getContent(),
 * getStatusCode(), and/or the headers property to access the result.
 *
 * @see template_preprocess_html()
 * @see \Drupal\Core\Render\AttachmentsResponseProcessorInterface
 * @see \Drupal\Core\Render\BareHtmlPageRenderer
 * @see \Drupal\Core\Render\HtmlResponse
 * @see \Drupal\Core\Render\MainContent\HtmlRenderer
 */
class AmpHtmlResponseAttachmentsProcessor extends HtmlResponseAttachmentsProcessor {

  /**
   * The route amp context to determine whether a route is an amp one.
   *
   * @var \Drupal\amp\Routing\AmpContext
   */
  protected $ampContext;

  /**
   * Constructs a HtmlResponseAttachmentsProcessor object.
   *
   * @param \Drupal\Core\Asset\AssetResolverInterface $asset_resolver
   *   An asset resolver.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $css_collection_renderer
   *   The CSS asset collection renderer.
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $js_collection_renderer
   *   The JS asset collection renderer.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\amp\Routing\AmpContext $amp_context
   *   The route amp context to determine whether the route is an amp one.
   */
  public function __construct(AssetResolverInterface $asset_resolver, ConfigFactoryInterface $config_factory, AssetCollectionRendererInterface $css_collection_renderer, AssetCollectionRendererInterface $js_collection_renderer, RequestStack $request_stack, RendererInterface $renderer, ModuleHandlerInterface $module_handler, AmpContext $amp_context) {
    parent::__construct($asset_resolver, $config_factory, $css_collection_renderer, $js_collection_renderer, $request_stack, $renderer, $module_handler);
    $this->ampContext = $amp_context;
  }

  /**
   * Processes asset libraries into render arrays.
   *
   * @param \Drupal\Core\Asset\AttachedAssetsInterface $assets
   *   The attached assets collection for the current response.
   * @param array $placeholders
   *   The placeholders that exist in the response.
   *
   * @return array
   *   An array keyed by asset type, with keys:
   *     - styles
   *     - scripts
   *     - scripts_bottom
   */
  protected function processAssetLibraries(AttachedAssetsInterface $assets, array $placeholders) {
    $variables = [];

    if ($this->ampContext->isAmpRoute()) {
      foreach ($assets->libraries as $delta => $library) {
        if (strpos($library, 'amp/') === FALSE) {
          unset($assets->libraries[$delta]);
        }
      }
      // Print amp scripts - if any are present.
      if (isset($placeholders['scripts']) || isset($placeholders['scripts_bottom'])) {
        // Do not optimize JS.
        $optimize_js = FALSE;
        list($js_assets_header, $js_assets_footer) = $this->assetResolver->getJsAssets($assets, $optimize_js);
        $variables['scripts'] = $this->jsCollectionRenderer->render($js_assets_header);
      }
    }
    else {
      $variables = parent::processAssetLibraries($assets, $placeholders);
    }

    return $variables;
  }

  /**
   * Transform a html_head_link array into html_head and http_header arrays.
   *
   * html_head_link is a special case of html_head which can be present as
   * a link item in the HTML head section, and also as a Link: HTTP header,
   * depending on options in the render array. Processing it can add to both the
   * html_head and http_header sections.
   *
   * @param array $html_head_link
   *   The 'html_head_link' value of a render array. Each head link is specified
   *   by a two-element array:
   *   - An array specifying the attributes of the link.
   *   - A boolean specifying whether the link should also be a Link: HTTP
   *     header.
   *
   * @return array
   *   An ['#attached'] section of a render array. This allows us to easily
   *   merge the results with other render arrays. The array could contain the
   *   following keys:
   *   - http_header
   *   - html_head
   */
  protected function processHtmlHeadLink(array $html_head_link) {
    $attached = parent::processHtmlHeadLink($html_head_link);
    if (array_key_exists('http_header', $attached)) {
      // Find the amphtml link and flag it to be displayed as a HTTP header.
      foreach ($attached['http_header'] as $key => $value) {
        if (strpos($value[1], 'rel="amphtml"') !== FALSE) {
          $new_value = str_replace(';', '', $value[1]);
          $attached['http_header'][$key] = ['Link', $new_value, TRUE];
        }
      }
    }
    return $attached;
  }

}
