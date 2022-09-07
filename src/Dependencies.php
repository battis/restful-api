<?php

namespace Battis\OAuth2\Server;

use Battis\OAuth2\Server\Repositories\UserRepository;
use Battis\OAuth2\Server\Repositories\AccessTokenRepository;
use Battis\OAuth2\Server\Repositories\AuthCodeRepository;
use Battis\OAuth2\Server\Repositories\ClientRepository;
use Battis\OAuth2\Server\Repositories\RefreshTokenRepository;
use Battis\OAuth2\Server\Repositories\ScopeRepository;
use Battis\UserSession;
use Battis\UserSession\Repositories\UserRepositoryInterface as UserSessionUserRepositoryInterface;
use Composer\Autoload\ClassLoader;
use DateInterval;
use DI\Container;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface as OAuth2ServerUserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use ReflectionClass;
use Slim\Views\PhpRenderer;

use function DI\autowire;
use function DI\create;
use function DI\get;

class Dependencies
{
  const DB_CONNECTION = "battis.oauth2Server.db";
  const DB_DRIVER = "driver";
  const DB_HOST = "host";
  const DB_PORT = "port";
  const DB_NAME = "dbname";
  const DB_USERNAME = "user";
  const DB_PASSWORD = "password";
  const DB_CHARSET = "charset";

  const PATH_PRIVATE_KEY = "battis.oauth2Sserver.pathToPrivateKey";
  const PATH_PUBLIC_KEY = "battis.oauth2Sserver.pathToPublicKey";
  const ENCRYPTION_KEY = "battis.oauth2Sserver.encryptionKey";

  const TTL_AUTH_CODE = "battis.oauth2Sserver.ttl.authCode";
  const TTL_ACCESS_TOKEN = "battis.oauth2Sserver.ttl.accessToken";
  const TTL_REFRESH_TOKEN = "battis.oauth2Sserver.ttl.refreshToken";

  const GRANT_TYPES = "battis.oauth2Sserver.grantTypes";

  private static $appRoot;

  private static function setDefaults(Container $container)
  {
    $reflection = new ReflectionClass(ClassLoader::class);
    self::$appRoot = dirname($reflection->getFileName(), 3);
    $var = self::$appRoot . "/var/oauth2";
    foreach (
      [
        self::PATH_PRIVATE_KEY => "$var/private.key",
        self::PATH_PUBLIC_KEY => "$var/public.key",
        self::TTL_AUTH_CODE => "PT5M",
        self::TTL_ACCESS_TOKEN => "PT1H",
        self::TTL_REFRESH_TOKEN => "P1M",
        self::GRANT_TYPES => [],
      ]
      as $key => $value
    ) {
      if (!$container->has($key)) {
        $container->set($key, $value);
      } else {
        $value = $container->get($key);
      }

      switch ($key) {
        case self::TTL_AUTH_CODE:
        case self::TTL_ACCESS_TOKEN:
        case self::TTL_REFRESH_TOKEN:
          if (false == $value instanceof DateInterval) {
            $container->set($key, new DateInterval($value));
          }
          break;
      }
    }
  }

  public static function prepare(Container $container)
  {
    self::setDefaults($container);

    $container->set(
      PhpRenderer::class,
      autowire()->constructor(
        self::$appRoot . "/vendor/battis/oauth2-server/templates"
      )
    );

    // prepare repository interface definitions
    foreach (
      [
        AccessTokenRepositoryInterface::class => AccessTokenRepository::class,
        AuthCodeRepositoryInterface::class => AuthCodeRepository::class,
        ClientRepositoryInterface::class => ClientRepository::class,
        RefreshTokenRepositoryInterface::class => RefreshTokenRepository::class,
        ScopeRepositoryInterface::class => ScopeRepository::class,
        OAuth2ServerUserRepositoryInterface::class => UserRepository::class,
      ]
      as $interface => $implementation
    ) {
      $container->set($interface, create($implementation));
    }
    $container->set(
      UserSessionUserRepositoryInterface::class,
      get(OAuth2ServerUserRepositoryInterface::class)
    );

    // prepare OAuth2 server definitions
    $container->set(
      AuthorizationServer::class,
      autowire()
        ->constructorParameter("privateKey", get(self::PATH_PRIVATE_KEY))
        ->constructorParameter("encryptionKey", get(self::ENCRYPTION_KEY))
    );

    $container->set(
      ResourceServer::class,
      autowire()->constructorParameter("publicKey", get(self::PATH_PUBLIC_KEY))
    );

    // prepare grant type definitions
    $container->set(
      AuthCodeGrant::class,
      autowire()->constructorParameter("authCodeTTL", get(self::TTL_AUTH_CODE))
    );

    $container->set(
      RefreshTokenGrant::class,
      autowire()->method("setRefreshTokenTTL", get(self::TTL_REFRESH_TOKEN))
    );

    // client credentials, implicit  and password grant types require no additional configuration

    // enable configured grant types
    /** @var AuthorizationServer $server */
    $server = $container->get(AuthorizationServer::class);
    foreach ($container->get(self::GRANT_TYPES) as $grantType) {
      $server->enableGrantType(
        $container->get($grantType),
        $container->get(self::TTL_ACCESS_TOKEN)
      );
    }

    // prepare UserSession definitions
    UserSession\Dependencies::prepare($container);
  }
}
