<?php

use Battis\OAuth2;
use DI\Container;
use Illuminate\Database\Capsule\Manager;
use Slim\Views\PhpRenderer;

use function DI\autowire;

/** @var Container $container */

$container->set(Manager::class, function () {
  $capsule = new Manager();
  $capsule->addConnection([
    "dsn" => "mysql:host=localhost;port=3306;dbname=example",
    "username" => "example",
    "password" => "s00p3rS3kr37",
  ]);
  $capsule->bootEloquent();
  return $capsule;
});

$container->set(
  PhpRenderer::class,
  autowire()->constructorParameter(
    "templatePath",
    __DIR__ . "/../../vendor/battis/oauth2-server/templates"
  )
);

OAuth2\Dependencies::prepare($container);
