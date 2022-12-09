<?php

namespace Battis\PHPUnit\Sessions\Tests;

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testHeaders()
    {
        header('Foo: Bar');
        $this->assertTrue(true);
    }

    public function testCookies()
    {
        $_COOKIE['foo'] = 'bar';
        $this->assertEquals('bar', $_COOKIE['foo']);
    }
}
