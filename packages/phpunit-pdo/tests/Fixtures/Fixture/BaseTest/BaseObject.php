<?php

namespace Battis\PHPUnit\PDO\Tests\Fixtures\Fixture\BaseTest;

use Battis\PHPUnit\PDO\Fixture\Base;

class BaseObject extends Base
{
    public array $array;

    protected function __construct(array $config)
    {
        $this->array = $config;
        $this->isIterableAs($this->array);
    }

    public static function fromArray(array $array): Base
    {
        return new BaseObject($array);
    }

    public function equals(Base $other): bool
    {
        if ($other instanceof BaseObject) {
            return $this->array == $other->array;
        }
        return false;
    }
}
