<?php

namespace Battis\OAuth2;

use Composer\Factory as ComposerFactory;
use DateInterval;
use Defuse\Crypto\Key;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\GrantTypeInterface;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Psr\Container\ContainerInterface;

class Settings
{
  const COMPOSER_PROJECT_ROOT = dirname(ComposerFactory::getComposerFile());
  const DEFAULT_PATH_TO_PRIVATE_KEY =
    self::COMPOSER_PROJECT_ROOT . "/var/oauth2/private.key";
  const DEFAULT_PATH_TO_PUBLIC_KEY =
    self::COMPOSER_PROJECT_ROOT . "/var/oauth2/public.key";
  const DEFAULT_TTL_AUTH_CODE = new DateInterval("PT5M");
  const DEFAULT_TTL_ACCESS_TOKEN = new DateInterval("PT1H");
  const DEFAULT_TTL_REFRESH_TOKEN = new DateInterval("P1M");
  const DEFAULT_GRANT_TYPES = [
    function (ContainerInterface $container) {
      /** @var Settings $settings */
      $settings = $container->get(Settings::class);
      return new AuthCodeGrant(
        $container->get(AuthCodeRepositoryInterface::class),
        $container->get(RefreshTokenRepositoryInterface::class),
        $settings->getAuthCodeTTL()
      );
    },
    function (ContainerInterface $container) {
      /** @var Settings $settings */
      $settings = $container->get(Settings::class);
      $grant = new RefreshTokenGrant(
        $container->get(RefreshTokenRepositoryInterface::class)
      );
      $grant->setRefreshTokenTTL($settings->getRefreshTokenTTL());
      return $grant;
    },
  ];

  /** @var string */
  private $pathToPrivateKey;

  /** @var string */
  private $pathToPublicKey;

  /** @var string|Key */
  private $encryptionKey = null;

  /** @var DateInterval */
  private $authCodeTTL;

  /** @var DateInterval */
  private $accessTokenTTL;

  /** @var DateInterval */
  private $refreshTokenTTL;

  /** @var Array<callable|GrantTypeInterface> */
  private $grantTypes;

  const PATH_TO_PRIVATE_KEY = "path_to_private_key";
  const PATH_TO_PUBLIC_KEY = "path_to_public_key";
  const ENCRYPTION_KEY = "encryption_key";
  const TTL_AUTH_CODE = "ttl_auth_code";
  const TTL_ACCESS_TOKEN = "ttl_access_token";
  const TTL_REFRESH_TOKEN = "ttl_refresh_token";
  const GRANT_TYPES = "grant_types";

  public function __construct(array $settings = [self::ENCRYPTION_KEY => null])
  {
    $this->pathToPrivateKey =
      $settings[self::PATH_TO_PRIVATE_KEY] ?: self::DEFAULT_PATH_TO_PRIVATE_KEY;
    $this->pathToPublicKey =
      $settings[self::PATH_TO_PUBLIC_KEY] ?: self::DEFAULT_PATH_TO_PUBLIC_KEY;
    $this->encryptionKey = $this->instantiate(
      $settings[self::ENCRYPTION_KEY],
      Key::class,
      [Key::class, "loadFromAsciiSafeString"]
    );
    $this->authCodeTTL = $this->instantiate($settings[self::TTL_AUTH_CODE]);
    $this->accessTokenTTL = $this->instantiate(
      $settings[self::TTL_ACCESS_TOKEN]
    );
    $this->refreshTokenTTL = $this->instantiate(
      $settings[self::TTL_REFRESH_TOKEN]
    );
    $this->grantTypes =
      $settings[self::GRANT_TYPES] ?: self::DEFAULT_GRANT_TYPES;
  }

  private function instantiate(
    $value,
    $class = DateInterval::class,
    $callable = null
  ) {
    if (is_string($value)) {
      if ($callable) {
        return call_user_func($callable, $value);
      }
      return new $class($value);
    } elseif ($value instanceof $class) {
      return $value;
    }
    return null;
  }

  public function getPathToPrivateKey()
  {
    return $this->pathToPrivateKey;
  }

  public function getPathToPublicKey()
  {
    return $this->pathToPublicKey;
  }

  public function getEncryptionKey()
  {
    return $this->encryptionKey;
  }

  public function getAuthCodeTTL()
  {
    return $this->authCodeTTL;
  }

  public function getAccessTokenTTL()
  {
    return $this->accessTokenTTL;
  }

  public function getRefreshTokenTTL()
  {
    return $this->refreshTokenTTL;
  }

  public function getGrantTypes()
  {
    return $this->grantTypes;
  }
}
