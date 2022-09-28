<?php

namespace Tests\Battis\CRUD\Utilities;

use Battis\CRUD\Utilities\Types;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TypesTest extends TestCase
{
    public function testToDatabaseValue()
    {
        foreach (
            [
                "foo bar baz" => "foo bar baz",
                0 => 0,
                "0" => "0",
                123 => 123,
                -123 => -123,
                true => true,
                false => false,
                '["a",1,null,false]' => '["a",1,null,false]',
            ]
            as $arg => $expected
        ) {
            $this->assertEquals($expected, Types::toDatabaseValue($arg));
        }
        $this->assertNull(Types::toDatabaseValue(null));
        $this->assertEquals("", Types::toDatabaseValue(""));
        $this->assertEquals(
            "1995-06-15 10:15:32",
            Types::toDatabaseValue(new DateTime("1995-06-15 10:15:32"))
        );
        $this->assertEquals(
            "1995-06-15 10:15:32",
            Types::toDatabaseValue(new DateTimeImmutable("1995-06-15 10:15:32"))
        );
        $this->assertEquals(
            '["a",1,null,false]',
            Types::toDatabaseValue(["a", 1, null, false])
        );
    }

    public function testToDatabaseValues()
    {
        $this->assertEquals(
            [
                "a" => "foo bar baz",
                "b" => 0,
                "c" => "0",
                "d" => 123,
                "e" => -123,
                "f" => true,
                "g" => false,
                "g2" => '["a",1,null,false]',
                "h" => null,
                "i" => "",
                "j" => "1995-06-15 10:15:32",
                "k" => "1995-06-15 10:15:32",
                "l" => '["a",1,null,false]',
            ],
            Types::toDatabaseValues([
                "a" => "foo bar baz",
                "b" => 0,
                "c" => "0",
                "d" => 123,
                "e" => -123,
                "f" => true,
                "g" => false,
                "g2" => '["a",1,null,false]',
                "h" => null,
                "i" => "",
                "j" => new DateTime("1995-06-15 10:15:32"),
                "k" => new DateTimeImmutable("1995-06-15 10:15:32"),
                "l" => ["a", 1, null, false],
            ])
        );
    }

    public function a(int $arg)
    {
    }
    public function b(string $arg)
    {
    }
    public function c(bool $arg)
    {
    }
    public function d(DateTime $arg)
    {
    }
    public function e(array $arg)
    {
    }
    public function f(string $arg1, DateTime $arg2)
    {
    }

    public function testToExpectedArgumentType()
    {
        $this->assertEquals(0, Types::toExpectedArgumentType($this, "a", 0));
        $this->assertEquals(
            "foo",
            Types::toExpectedArgumentType($this, "b", "foo")
        );
        $this->assertEquals(
            false,
            Types::toExpectedArgumentType($this, "c", false)
        );
        $this->assertEquals(
            new DateTime("1995-06-15 10:15:32"),
            Types::toExpectedArgumentType($this, "d", "1995-06-15 10:15:32")
        );
        $this->assertEquals(
            ["a", 1, null, false],
            Types::toExpectedArgumentType($this, "e", '["a",1,null,false]')
        );
        $this->assertEquals(
            '["a",1,null,false]',
            Types::toExpectedArgumentType($this, "a", '["a",1,null,false]')
        );
        $this->assertEquals(
            "1995-06-15 10:15:32",
            Types::toExpectedArgumentType($this, "f", "1995-06-15 10:15:32")
        );
        $this->assertEquals(
            new DateTime("1995-06-15 10:15:32"),
            Types::toExpectedArgumentType($this, "f", "1995-06-15 10:15:32", 1)
        );
    }
}
