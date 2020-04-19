<?php


namespace Battis\RestfulAPI\Authentication;


use Battis\PersistentObject\PersistentObjectException;
use Battis\PersistentObject\PerUser\User;
use Battis\RestfulAPI\RestfulObject;
use Battis\RestfulAPI\RestfulUser;
use DateTime;
use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\RequestInterface;

class JWTOperations
{
    public const
        COOKIE_REFRESH = 'refresh';
    public const
        ATTR_TOKEN = 'token';
    public const
        PROP_TOKEN = 'token',
        PROP_EXPIRES = 'expires';

    /** RFC 7519 claims */
    public const
        CLAIM_ISSUER = 'issuer',
        CLAIM_SUBJECT = 'sub',
        CLAIM_AUDIENCE = 'aud',
        CLAIM_IDENTIFIER = 'jti',
        CLAIM_ISSUED_AT = 'iat',
        CLAIM_NOT_BEFORE = 'nbf',
        CLAIM_EXPIRATION = 'exp';
    /** Custom public claims */
    public const
        CLAIM_USER_ID = 'x-battis-uid';

    public const IDENTIFIER_BLACKLIST = [];

    private static $tokenServer;

    /**
     * @param RestfulUser $user
     * @return array
     */
    public static function getApiToken(RestfulUser $user): array
    {
        $claims = [
            self::CLAIM_USER_ID => $user->getId()
        ];
        $apiTokenData = self::generateToken($claims, getenv('API_TOKEN_DURATION_IN_MINUTES'));
        self::setRefreshToken($claims);

        return $apiTokenData;
    }

    /**
     * @param RequestInterface $request
     * @param array $claims
     * @return bool
     * @throws PersistentObjectException
     */
    public static function validateTokenClaims(RequestInterface $request, array $claims = []): bool
    {
        $token = $request->getAttribute(self::ATTR_TOKEN);
        if (self::validateToken($token, $claims)) {
            RestfulObject::assignUser($token[self::CLAIM_USER_ID]);
            return true;
        }
        return false;
    }

    public static function refreshApiToken(RestfulUser $user, $token)
    {
        if (self::validateRefreshToken($token)) {
            try {
                return self::getApiToken($user);
            } catch (Exception $e) {
                // do nothing
            }
        }
        return false;
    }

    private static function getTokenIdentifier(): string
    {
        return self::getTokenServer() . '@v1';
    }

    private static function getTokenServer(): string
    {
        if (empty(self::$tokenServer)) {
            self::$tokenServer = "https://{$_SERVER['HTTP_HOST']}" . getenv('ROOT_PATH');
        }
        return self::$tokenServer;
    }

    private static function generateToken(array $claims = [], int $minutesDuration = 15)
    {
        $server = self::getTokenServer();
        if (empty($audience)) {
            $audience = $server;
        }

        $minutesDuration = max(1, $minutesDuration);
        $now = (new DateTime())->getTimestamp();
        $expires = (new DateTime("now +$minutesDuration minutes"))->getTimestamp();

        $claims = array_merge(
            [ // claims that can be overridden by proposals
                self::CLAIM_NOT_BEFORE => $now,
                self::CLAIM_AUDIENCE => $audience,
            ],
            // claims that were proposed as argument
            $claims,
            [ // claims that override any proposals
                self::CLAIM_ISSUER => $server,
                self::CLAIM_IDENTIFIER => self::getTokenIdentifier(),
                self::CLAIM_ISSUED_AT => $now,
                self::CLAIM_EXPIRATION => $expires
            ]);

        return [
            self::PROP_TOKEN => JWT::encode($claims, getenv('JWT_SECRET')),
            self::PROP_EXPIRES => $expires
        ];
    }

    private static function setRefreshToken(array $apiTokenClaims)
    {
        $refreshTokenData = self::generateToken($apiTokenClaims, getenv('REFRESH_TOKEN_DURATION_IN_MINUTES'));
        $cookieOptions = [
            'expires' => 1,
            'domain' => $_SERVER['HTTP_HOST'],
            'path' => getenv('ROOT_PATH'),
            'secure' => true,
            'samesite' => 'Strict',
            'httponly' => true
        ];
        setcookie(
            self::COOKIE_REFRESH,
            $refreshTokenData[self::PROP_TOKEN],
            array_merge($cookieOptions, ['expires' => $refreshTokenData[self::PROP_EXPIRES]])
        );

    }

    private static function validateToken(array $token, array $claims): bool
    {
        $claims = array_merge(
            $claims,
            [
                self::CLAIM_AUDIENCE => self::getTokenServer(),
                self::CLAIM_IDENTIFIER => function ($identifier) {
                    return false === in_array($identifier, self::IDENTIFIER_BLACKLIST);
                }
            ]
        );

        foreach ($claims as $claim => $value) {
            if (empty($token[$claim]) || (is_string($value) && $token[$claim] !== $value) || (is_callable($value) && $value($token[$claim]) === false)) {
                return false;
            }
        }
        return true;
    }

    private static function validateRefreshToken($refreshToken)
    {
        return self::validateToken($refreshToken, []);
    }

}
