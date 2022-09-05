<?php

// values loaded from the environment or individual library config files

use Battis\OAuth2\Server\Dependencies as OAuth2;
use Defuse\Crypto\Key;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;

// FIXME better to load from $_ENV than to hard code!
return [
  OAuth2::DB_DSN => "mysql:host=localhost;port=3306;dbname=example",
  OAuth2::DB_USERNAME => "example",
  OAuth2::DB_PASSWORD => "s00p3rS3kr37",
  OAuth2::ENCRYPTION_KEY => fn() => Key::loadFromAsciiSafeString(
    "def00000b1a6feeefadb00454998cd54c98bd8e0d0aa5f0466679a58545d52c66d49a890f30d7087de5d679721c230b358a76937977e30a3c42004b70e94583255c67023"
  ),
  OAuth2::TTL_REFRESH_TOKEN => "P6W",
  OAuth2::GRANT_TYPES => [AuthCodeGrant::class, RefreshTokenGrant::class],
];
