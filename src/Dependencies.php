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
use Defuse\Crypto\Key;
use Doctrine\DBAL\Connection;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
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

class Dependencies
{
    const TTL_AUTH_CODE = "battis.oauth2.server.ttl.authCode";
    const TTL_ACCESS_TOKEN = "battis.oauth2.server.ttl.accessToken";
    const TTL_REFRESH_TOKEN = "battis.oauth2.server.ttl.refreshToken";
    const GRANT_TYPES = "battis.oauth2.server.grantTypes";
    const PRIVATE_KEY = "battis.oauth2.server.privateKey";
    const PUBLIC_KEY = "battis.oauth2.server.publicKey";
    const ENCRYPTION_KEY = "battis.oauth2.server.encryptionKey";
    const ENCRYPTION_KEY_STRING = "string";
    const ENCRYPTION_KEY_PATH_TO_STRING = "pathToString";
    const ENCRYPTION_KEY_DEFUSE = "defuse";
    const ENCRYPTION_KEY_PATH_TO_DEFUSE = "pathToDefuse";
    const TEMPLATES = "battis.oauth2.server.templates";

    private static $pathToApp;

    /**
     * @param string|DateInterval $authCodeTTL
     * @param string|DateInterval $accessTokenTTL
     * @param string|DateInterval $refreshTokenTTL
     * @param string|CryptKey $pathToPrivateKey
     * @param string|CryptKey $pathToPublicKey
     * @param string|Key $encryptionKey
     * @param string $encryptionKeyType
     * @param string $pathToTemplates
     * @return array
     */
    public static function definitions(
        $authCodeTTL = null,
        $accessTokenTTL = null,
        $refreshTokenTTL = null,
        array $grantTypes = [AuthCodeGrant::class, RefreshTokenGrant::class],
        $privateKey = null,
        $publicKey = null,
        $encryptionKey = null,
        string $encryptionKeyType = self::ENCRYPTION_KEY_PATH_TO_DEFUSE,
        string $pathToTemplates = "{PACKAGE_ROOT}/templates"
    ): array {
        $authCodeTTL = self::toDateInterval($authCodeTTL ?? "PT5M");
        $accessTokenTTL = self::toDateInterval($accessTokenTTL ?? "PT1H");
        $refreshTokenTTL = self::toDateInterval($refreshTokenTTL ?? "P1M");

        if (!($privateKey instanceof CryptKey)) {
            $privateKey = self::expandPath(
                $privateKey ?? "{APP_ROOT}/var/oauth2/private.key"
            );
        }

        if (!($publicKey instanceof CryptKey)) {
            $publicKey = self::expandPath(
                $publicKey ?? "{APP_ROOT}/var/oauth2/public.key"
            );
        }

        $encryptionKey = self::toKey(
            $encryptionKey ?? "{APP_ROOT}/var/oauth2/encryption.key",
            $encryptionKeyType
        );

        $pathToTemplates = self::expandPath($pathToTemplates);

        return array_merge(UserSession\Dependencies::definitions(), [
            // settings
            self::ENCRYPTION_KEY => fn() => $encryptionKey,
            self::TTL_AUTH_CODE => fn() => $authCodeTTL,
            self::TTL_ACCESS_TOKEN => fn() => $accessTokenTTL,
            self::TTL_REFRESH_TOKEN => fn() => $refreshTokenTTL,
            self::GRANT_TYPES => fn() => $grantTypes,
            self::PRIVATE_KEY => fn() => $privateKey,
            self::PUBLIC_KEY => fn() => $publicKey,
            self::TEMPLATES => fn() => $pathToTemplates,

            //battis/users-session implementations
            UserSession\Repositories\UserRepositoryInterface::class => fn(
                Connection $connection
            ) => new UserRepository($connection),
            PhpRenderer::class => function (ContainerInterface $container) {
                return new PhpRenderer($container->get(self::TEMPLATES));
            },

            // league/oauth2-server implementations
            UserRepositoryInterface::class => fn(
                Connection $connection
            ) => new UserRepository($connection),
            ClientRepositoryInterface::class => fn(
                Connection $connection
            ) => new ClientRepository($connection),
            ScopeRepositoryInterface::class => fn(
                Connection $connection,
                UserRepositoryInterface $userRepo
            ) => new ScopeRepository($connection, $userRepo),
            AuthCodeRepositoryInterface::class => fn(
                Connection $connection
            ) => new AuthCodeRepository($connection),
            AccessTokenRepositoryInterface::class => fn(
                Connection $connection
            ) => new AccessTokenRepository($connection),
            RefreshTokenRepositoryInterface::class => fn(
                Connection $connection
            ) => new RefreshTokenRepository($connection),

            // apply settings to league/oauth2-server services
            AuthCodeGrant::class => function (
                ContainerInterface $container,
                AuthCodeRepositoryInterface $authCodeRepo,
                RefreshTokenRepositoryInterface $refreshTokenRepo
            ) {
                return new AuthCodeGrant(
                    $authCodeRepo,
                    $refreshTokenRepo,
                    $container->get(self::TTL_REFRESH_TOKEN)
                );
            },
            RefreshTokenGrant::class => function (
                ContainerInterface $container,
                RefreshTokenRepositoryInterface $refreshTokenRepo
            ) {
                $grant = new RefreshTokenGrant($refreshTokenRepo);
                $grant->setRefreshTokenTTL(
                    $container->get(self::TTL_REFRESH_TOKEN)
                );
                return $grant;
            },
            AuthorizationServer::class => function (
                ContainerInterface $container,
                ClientRepositoryInterface $clientRepo,
                AccessTokenRepositoryInterface $accessTokenRepo,
                ScopeRepositoryInterface $scopeRepo
            ) {
                $server = new AuthorizationServer(
                    $clientRepo,
                    $accessTokenRepo,
                    $scopeRepo,
                    $container->get(self::PRIVATE_KEY),
                    $container->get(self::ENCRYPTION_KEY)
                );
                foreach ($container->get(self::GRANT_TYPES) as $grantType) {
                    $server->enableGrantType(
                        $container->get($grantType),
                        $container->get(self::TTL_ACCESS_TOKEN)
                    );
                }
                return $server;
            },
            ResourceServer::class => function (
                ContainerInterface $container,
                AccessTokenRepositoryInterface $accessTokenRepo
            ) {
                return new ResourceServer(
                    $accessTokenRepo,
                    $container->get(self::PUBLIC_KEY)
                );
            },
        ]);
    }

    private static function toKey($key, $type)
    {
        $defuse = false;
        switch ($type) {
            case self::ENCRYPTION_KEY_PATH_TO_DEFUSE:
                $defuse = true;
            case self::ENCRYPTION_KEY_PATH_TO_STRING:
                $key = file_get_contents(self::expandPath($key));
                if (!$defuse) {
                    return $key;
                }
            case self::ENCRYPTION_KEY_DEFUSE:
                if (is_string($key)) {
                    $key = Key::loadFromAsciiSafeString($key);
                }
                return $key;
            case self::ENCRYPTION_KEY_STRING:
            default:
                return $key;
        }
    }

    private static function toDateInterval($value)
    {
        if ($value instanceof DateInterval) {
            return $value;
        } else {
            return new DateInterval($value);
        }
    }

    private static function expandPath($path)
    {
        if (empty(self::$pathToApp)) {
            self::$pathToApp = dirname(
                (new ReflectionClass(ClassLoader::class))->getFileName(),
                3
            );
        }

        foreach (
            [
                "APP_ROOT" => self::$pathToApp,
                "PACKAGE_ROOT" =>
                    self::$pathToApp . "/vendor/battis/oauth2-server",
            ]
            as $placeholder => $placeholderPath
        ) {
            $path = preg_replace(
                "/\{$placeholder\}/i",
                $placeholderPath,
                $path
            );
        }
        return $path;
    }
}
