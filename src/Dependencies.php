<?php

namespace Battis\OAuth2;

use Battis\OAuth2\Repositories\AccessTokenRepository;
use Battis\OAuth2\Repositories\AuthCodeRepository;
use Battis\OAuth2\Repositories\ClientRepository;
use Battis\OAuth2\Repositories\RefreshTokenRepository;
use Battis\OAuth2\Repositories\ScopeRepository;
use Battis\OAuth2\Repositories\UserRepository;
use DI\Container;
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

use function DI\autowire;
use function DI\create;
use function DI\get;

class Dependencies
{
  public static function prepare(Container $container)
  {
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
      if (false == $container->has($interface)) {
        $container->set($interface, create($implementation));
      }
    }

    // prepare to inject OAuth2 servers
    if (false == $container->has(AuthorizationServer::class)) {
      $container->set(
        AuthorizationServer::class,
        autowire()
          ->constructorParameter("privateKey", get("oauth2.privateKey"))
          ->constructorParameter("encryptionKey", get("oauth2.encryptionKey"))
      );
    }

    if (false == $container->has(ResourceServer::class)) {
      $container->set(
        ResourceServer::class,
        autowire()->constructorParameter("publicKey", get("oauth2.publicKey"))
      );
    }

    // prepare to inject grant types
    if (false == $container->has(AuthCodeGrant::class)) {
      $container->set(
        AuthCodeGrant::class,
        autowire()->constructorParameter(
          "authCodeTTL",
          get("oauth2.ttl.authCode")
        )
      );
    }

    if (false == $container->has(RefreshTokenGrant::class)) {
      $container->set(RefreshTokenGrant::class, function (
        ContainerInterface $container
      ) {
        $grant = new RefreshTokenGrant(
          $container->get(RefreshTokenRepositoryInterface::class)
        );
        $grant->setRefreshTokenTTL($container->get("oauth2.ttl.refreshToken"));
        return $grant;
      });
    }

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
