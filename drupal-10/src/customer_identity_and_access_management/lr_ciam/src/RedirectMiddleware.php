<?php

namespace Drupal\lr_ciam;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;

/**
 * Executes redirect before the main kernel takes over the request.
 */
class RedirectMiddleware implements HttpKernelInterface {

  /**
   * The wrapped HTTP kernel.
   *
   * @var HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * The redirect URL.
   *
   * @var RedirectResponse
   */
  protected $redirectResponse;

  /**
   * Constructs a RedirectMiddleware
   * object.
   *
   * @param HttpKernelInterface $http_kernel
   *   The decorated kernel.
   */
  public function __construct(HttpKernelInterface $http_kernel) {
    $this->httpKernel = $http_kernel;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response {
    $response = $this->httpKernel->handle($request, $type, $catch);
    return $this->redirectResponse ?: $response;
  }

  /**
   * Stores the requested redirect response.
   *
   * @param RedirectResponse|null $redirectResponse
   *   Redirect response.
   */
  public function setRedirectResponse(?RedirectResponse $redirectResponse) {
    $this->redirectResponse = $redirectResponse;
  }

}
