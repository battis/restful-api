<?php

// could be configured to load from environment variables or different configurations
return [
  "paths" => [
    "migrations" => [
      "%%PHINX_CONFIG_DIR%%/db/migrations",
      "%%PHINX_CONFIG_DIR%%/vendor/battis/oauth2-server/db/migrations",
    ],
    "seeds" => "%%PHINX_CONFIG_DIR%%/db/seeds",
  ],
  "environments" => [
    "default_migration_table" => "phinxlog",
    "default_environment" => "development",
    "development" => [
      "adapter" => "mysql",
      "host" => "localhost",
      "name" => "example",
      "user" => "example",
      "pass" => "s00p3rS3kr37",
      "port" => "3306",
      "charset" => "utf8",
    ],
  ],
  "version_order" => "creation",
];
