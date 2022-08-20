<?php

use Dotenv\Dotenv;

require_once __DIR__ . "/vendor/autoload.php";

Dotenv::createImmutable(__DIR__)->load();

return [
    "paths" => [
        "migrations" => "%%PHINX_CONFIG_DIR%%/db/migrations",
        "seeds" => "%%PHINX_CONFIG_DIR%%/db/seeds",
    ],
    "environments" => [
        "default_migration_table" => "phinxlog",
        "default_environment" => "development",
        "production" => [
            "adapter" => $_ENV["DB__CONNECTION"],
            "host" => $_ENV["DB__HOST"],
            "name" => $_ENV["DB__DATABASE"],
            "user" => $_ENV["DB__USER"],
            "pass" => $_ENV["DB__PASSWORD"],
            "port" => $_ENV["DB__PORT"],
            "charset" => $_ENV["DB__CHARSET"],
        ],
    ],
    "version_order" => "creation",
];
