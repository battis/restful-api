<?php

namespace Battis\PHPUnit\Sessions;

class Bootstrap
{
    private function __construct()
    {
    }

    public static function execute(): void
    {
        global $_COOKIE, $_SESSION;
        if (php_sapi_name() === "cli") {
            if (!isset($_COOKIE)) {
                $_COOKIE = [];
            }
            if (!isset($_SESSION)) {
                $_SESSION = [];
            }
        }

        ob_start();
    }
}
