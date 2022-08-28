<?php

// values loaded from the environment or individual library config files
return [
  PDO::class => [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => 3306,
    "database" => "example",
    "username" => "example",
    "password" => "s00p3rS3kr37",
    "dsn" => "mysql:host=localhost;port=3306;dbname=example",
  ],
];
