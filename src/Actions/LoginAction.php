<?php

namespace Battis\OAuth2\Actions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\PhpRenderer;

class LoginAction
{
  private $renderer;

  public function __construct(PhpRenderer $renderer)
  {
    $this->renderer = $renderer;
  }

  public function __invoke(
    RequestInterface $request,
    ResponseInterface $response
  ) {
    return $this->renderer->render($response, "login.php");
  }
}
