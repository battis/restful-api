<?php

namespace Battis\OAuth2;

use Battis\OAuth2\Actions;

class Controller
{
  const ENDPOINT = "/oauth2";

  public function __invoke($oauth2)
  {
    $oauth2->get("/login", Actions\LoginAction::class);
    $oauth2->post("/auth", Actions\AuthorizeAction::class);
    $oauth2->post("/token", Actions\AccessTokenAction::class);
  }
}
