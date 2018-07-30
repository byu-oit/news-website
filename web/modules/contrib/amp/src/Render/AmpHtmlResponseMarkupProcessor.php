<?php

namespace Drupal\amp\Render;

use Drupal\amp\Service\AMPService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\HtmlResponse;
use Lullabot\AMP\Validate\Scope;
use Psr\Log\LoggerInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Processes markup of HTML responses.
 *
 */
class AmpHtmlResponseMarkupProcessor extends ServiceProviderBase  {

  /**
   * The original content.
   *
   * @var string
   */
  protected $content;

  /**
   * The AMP-processed content.
   *
   * @var string
   */
  protected $ampContent;


  /**
   * The AMP library service.
   *
   * @var AMPService
   */
  protected $ampLibraryService;

  /**
   * The AMP library converter.
   *
   * @var \Lullabot\AMP\AMP
   */
  protected $ampConverter;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $ampConfig;

  /**
   * Constructs an AmpHtmlResponseMarkupProcessor object.
   *
   * @param AMPService $amp_library_service
   *   An amp library service.
   *
   */
  public function __construct(AMPService $amp_library_service, LoggerInterface $logger, ConfigFactoryInterface $configFactoryInterface) {
    $this->ampService = $amp_library_service;
    $this->logger = $logger;
    $this->configFactory = $configFactoryInterface;
    $this->ampConfig = $this->configFactory->get('amp.settings');
  }

  /**
   * Processes the content of a response into amp html.
   *
   * @param \Drupal\Core\Render\HtmlResponse $response
   *   The response to process.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The processed response, with the content updated to amp markup.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the $response parameter is not the type of response object
   *   the processor expects.
   */
  public function processMarkupToAmp(HtmlResponse $response) {

    if (!$response instanceof HtmlResponse) {
      throw new \InvalidArgumentException('\Drupal\Core\Render\HtmlResponse instance expected.');
    }

    // Get a reference to the content.
    $this->content = $response->getContent();

    // First check the config if full html warnings are on, if not then exit with unaltered response
    if (!$this->ampConfig->get('amp_library_process_full_html')) {
      return $response;
    }

    $options = ['scope' => Scope::HTML_SCOPE];
    if ($this->ampConfig->get('amp_library_process_statistics')) {
      $options += ['add_stats_html_comment' => true];
    }

    $this->ampConverter = $this->ampService->createAMPConverter();

    $this->ampConverter->loadHtml($this->content, $options);

    $this->ampContent = $this->ampConverter->convertToAmpHtml();
    $request_uri = \Drupal::request()->getRequestUri();

    $heading = "<h3>AMP PHP Library messages for $request_uri</h3>" . PHP_EOL;
    $heading .= 'To disable these notices goto <a href="/admin/config/content/amp">AMP configuration</a> and uncheck '
        . '<em>Debugging: Add a notice in the Drupal log ...</em> in the AMP PHP Library Configuration fieldset' . PHP_EOL;

    if ($this->ampConfig->get('amp_library_process_full_html_warnings')) {
      // Add any warnings that were generated
      $this->logger->notice("$heading <pre>" . $this->ampConverter->warningsHumanHtml() . '</pre>');
    }

    // Return the processed content.
    $response->setContent($this->ampContent);

    return $response;
  }
}
