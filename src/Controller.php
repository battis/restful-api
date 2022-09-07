<?php

namespace Battis\OAuth2\Server;

use Battis\OAuth2\Server\Actions;
use Battis\UserSession;
use Battis\UserSession\Middleware\RequireAuthentication;

class Controller
{
  const ENDPOINT = "/";

  public function __invoke($api)
  {
    $api->group(
      basename(UserSession\Controller::ENDPOINT),
      UserSession\Controller::class
    );
    $api->group("oauth2", function ($oauth2) {
      $oauth2
        ->get("/authorize", Actions\AuthorizeCodeGrant::class)
        ->add(RequireAuthentication::class);
      $oauth2->get("/access_token", Actions\AcquireAccessToken::class);
    });
  }
}
