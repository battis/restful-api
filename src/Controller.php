<?php

namespace Battis\OAuth2;

use Battis\OAuth2\Actions;

class Controller
{
  const ENDPOINT = "/oauth2";

  public function __invoke($routeGroup)
  {
    $routeGroup->post("/auth", Actions\Authorize::class);
    $routeGroup->post("/token", Actions\AccessToken::class);
  }
}
