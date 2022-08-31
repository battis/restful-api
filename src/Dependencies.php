<?php

namespace Battis\OAuth2;

use Battis\OAuth2\Repositories\AccessTokenRepository;
use Battis\OAuth2\Repositories\AuthCodeRepository;
use Battis\OAuth2\Repositories\ClientRepository;
use Battis\OAuth2\Repositories\RefreshTokenRepository;
use Battis\OAuth2\Repositories\ScopeRepository;
use Battis\OAuth2\Repositories\UserRepository;
use DI\Container;
use Illuminate\Database\Capsule\Manager;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Slim\Views\PhpRenderer;

use function DI\autowire;
use function DI\create;
use function DI\get;

class Dependencies
{
  public static function prepare(Container $container)
  {
    // prepare Eloquent ORM manager
    $container->set(Manager::class, function (ContainerInterface $container) {
      $capsule = new Manager();
      $capsule->addConnection([
        "dsn" => $container->get("db.dsn"),
        "username" => $container->get("db.username"),
        "password" => $container->get("db.password"),
      ]);
      $capsule->bootEloquent();
      return $capsule;
    });

    // prepare Slim PHP template renderer (for login & authorize endpoints)
    $container->set(
      PhpRenderer::class,
      autowire()->constructorParameter(
        "templatePath",
        $container->get("composer.projectRoot") .
          "/vendor/battis/oauth2-server/templates"
      )
    );

    // prepare to inject repository implementations
    foreach (
      [
        AccessTokenRepositoryInterface::class => AccessTokenRepository::class,
        AuthCodeRepositoryInterface::class => AuthCodeRepository::class,
        ClientRepositoryInterface::class => ClientRepository::class,
        RefreshTokenRepositoryInterface::class => RefreshTokenRepository::class,
        ScopeRepositoryInterface::class => ScopeRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
      ]
      as $interface => $implementation
    ) {
      $container->set($interface, create($implementation));
    }

    // prepare to inject OAuth2 servers
    $container->set(
      AuthorizationServer::class,
      autowire()
        ->constructorParameter("privateKey", get("oauth2.privateKey"))
        ->constructorParameter("encryptionKey", get("oauth2.encryptionKey"))
    );

    $container->set(
      ResourceServer::class,
      autowire()->constructorParameter("publicKey", get("oauth2.publicKey"))
    );

    // prepare to inject grant types
    $container->set(
      AuthCodeGrant::class,
      autowire()->constructorParameter(
        "authCodeTTL",
        get("oauth2.ttl.authCode")
      )
    );

    $container->set(RefreshTokenGrant::class, function (
      ContainerInterface $container
    ) {
      $grant = new RefreshTokenGrant(
        $container->get(RefreshTokenRepositoryInterface::class)
      );
      $grant->setRefreshTokenTTL($container->get("oauth2.ttl.refreshToken"));
      return $grant;
    });

    // client credentials, implicit  and password grant types require no additional configuration

    // enable configured grant types
    /** @var AuthorizationServer $server */
    $server = $container->get(AuthorizationServer::class);
    foreach ($container->get("oauth2.grantTypes") as $grantType) {
      $server->enableGrantType(
        $container->get($grantType),
        $container->get("oauth2.ttl.accessToken")
      );
    }
  }
}
