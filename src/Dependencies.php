<?php

namespace Battis\OAuth2\Server;

use Battis\OAuth2\Server\Repositories\AccessTokenRepository;
use Battis\OAuth2\Server\Repositories\AuthCodeRepository;
use Battis\OAuth2\Server\Repositories\ClientRepository;
use Battis\OAuth2\Server\Repositories\RefreshTokenRepository;
use Battis\OAuth2\Server\Repositories\ScopeRepository;
use Battis\OAuth2\Server\Repositories\UserRepository;
use Battis\UserSession;
use Composer\Autoload\ClassLoader;
use DateInterval;
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
use ReflectionClass;
use Slim\Views\PhpRenderer;

use function DI\autowire;
use function DI\create;
use function DI\get;

class Dependencies
{
  const DB_DSN = "battis.oauth2Server.db.dsn";
  const DB_USERNAME = "battis.oauth2Sserver.db.userName";
  const DB_PASSWORD = "battis.oauth2Sserver.db.password";

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
      autowire()->constructorParameter(
        "templatePath",
        self::$appRoot . "/vendor/battis/oauth2-server/templates"
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
        ->constructorParameter("privateKey", get(self::PATH_PRIVATE_KEY))
        ->constructorParameter("encryptionKey", get(self::ENCRYPTION_KEY))
    );

    $container->set(
      ResourceServer::class,
      autowire()->constructorParameter("publicKey", get(self::PATH_PUBLIC_KEY))
    );

    // prepare to inject grant types
    $container->set(
      AuthCodeGrant::class,
      autowire()->constructorParameter("authCodeTTL", get(self::TTL_AUTH_CODE))
    );

    $container->set(RefreshTokenGrant::class, function (
      ContainerInterface $container
    ) {
      $grant = new RefreshTokenGrant(
        $container->get(RefreshTokenRepositoryInterface::class)
      );
      $grant->setRefreshTokenTTL($container->get(self::TTL_REFRESH_TOKEN));
      return $grant;
    });

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

    // prepare Eloquent ORM manager
    if (!$container->has(Manager::class)) {
      $container->set(Manager::class, function (ContainerInterface $container) {
        $capsule = new Manager();
        $capsule->addConnection([
          "dsn" => $container->get(self::DB_DSN),
          "username" => $container->get(self::DB_USERNAME),
          "password" => $container->get(self::DB_PASSWORD),
        ]);
        $capsule->bootEloquent();
        return $capsule;
      });
    }

    // prepare UserSession
    UserSession\Dependencies::prepare($container);
  }
}
