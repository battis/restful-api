<?php

namespace Battis\PHPUnit\Sessions;

use PHPUnit\Runner\AfterLastTestHook;

class Extension implements AfterLastTestHook
{
    public function executeAfterLastTest(): void
    {
        if (ob_get_level() > 0) {
            @ob_flush();
        }
    }
}
