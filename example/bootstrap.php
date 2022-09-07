<?php

use Battis\OAuth2\Server\Dependencies as OAuth2;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;

require_once __DIR__ . "/vendor/autoload.php";

$container = (new ContainerBuilder())
  ->addDefinitions(include __DIR__ . "/config/settings.php")
  ->build();

$container->set(EntityManager::class, function (ContainerInterface $c) {
  return EntityManager::create(
    $c->get(OAuth2::DB_CONNECTION),
    ORMSetup::createAnnotationMetadataConfiguration(
      $c->get("doctrine.metadataDirs", $c->get("doctrine.devMode"))
    )
  );
});

return $container;
