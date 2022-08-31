<?php

// values loaded from the environment or individual library config files

use Composer\Autoload\ClassLoader;
use Defuse\Crypto\Key;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;

$reflection = new ReflectionClass(ClassLoader::class);
$projectRoot = dirname($reflection->getFileName(), 3);

return [
  "composer.projectRoot" => $projectRoot,
  "oauth2.privateKey" => "$projectRoot/var/oauth2/private.key",
  "oauth2.publicKey" => "$projectRoot/var/oauth2/public.key",
  "oauth2.encryptionKey" => Key::loadFromAsciiSafeString(
    "def00000b1a6feeefadb00454998cd54c98bd8e0d0aa5f0466679a58545d52c66d49a890f30d7087de5d679721c230b358a76937977e30a3c42004b70e94583255c67023"
  ),
  "oauth2.ttl.authCode" => new DateInterval("PT5M"),
  "oauth2.ttl.accessToken" => new DateInterval("PT1H"),
  "oauth2.ttl.refreshToken" => new DateInterval("P1M"),
  "oauth2.grantTypes" => [AuthCodeGrant::class, RefreshTokenGrant::class],
];
