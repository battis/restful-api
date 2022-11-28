<?php

namespace Battis\PHPUnit\Sessions;

use PHPUnit\Runner\AfterLastTestHook;

class Extension implements AfterLastTestHook
{
    public function executeAfterLastTest(): void
    {
        ob_flush();
    }
}
