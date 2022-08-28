<?php

use Battis\OAuth2;
use DI\Container;
use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;

/** @var Container $container */

$container->set(PDO::class, function (ContainerInterface $container) {
  $settings = $container->get("settings")[PDO::class];

  $pdo = new PDO(
    $settings["dsn"],
    $settings["username"],
    $settings["password"]
  );
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

  if (strpos($settings["dsn"], "sqlite") === 0) {
    $pdo->exec("PRAGMA foreign_keys = ON");
  }

  return $pdo;
});

$container->set(Manager::class, function (ContainerInterface $container) {
  $capsule = new Manager();
  $settings = $container->get(PDO::class);
  $capsule->addConnection(array_filter($settings));
  $capsule->bootEloquent();
  return $capsule;
});

// Configure OAuth2\Settings as needed (as well as any other alternate dependency implementations)
$container->set(
  OAuth2\Settings::class,
  fn() => new OAuth2\Settings([
    OAuth2\Settings::ENCRYPTION_KEY =>
      "def00000b1a6feeefadb00454998cd54c98bd8e0d0aa5f0466679a58545d52c66d49a890f30d7087de5d679721c230b358a76937977e30a3c42004b70e94583255c67023",
  ])
);

// ...and then prepare the container with the remaining dependencies
OAuth2\Dependencies::prepare($container);
