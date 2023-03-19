<?php

namespace Battis\PHPUnit\PDO\Tests\Fixtures\Fixture\SchemaTest;

use Battis\PHPUnit\PDO\Fixture\Base;
use Battis\PHPUnit\PDO\Fixture\Schema;
use PDO;

class SchemaObject extends Schema
{
    public array $array;

    public function __construct(array $array)
    {
        $this->array = $array;
        $this->isIterableAs($this->array);
    }

    public static function fromArray(array $array): SchemaObject
    {
        return new SchemaObject($array);
    }

    public function setUp(PDO $pdo): void
    {
    }

    public function tearDown(PDO $pdo): void
    {
    }

    public function equals(Base $other): bool
    {
        if ($other instanceof SchemaObject) {
            return $this->array == $other->array;
        }
        return false;
    }
}
