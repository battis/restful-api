<?php

namespace Battis\PHPUnit\PDO\Traits;

use Battis\PHPUnit\PDO\Fixture\Fixture as PDOFixture;
use Battis\PHPUnit\PDO\Traits\PDO;

trait Fixture
{
    use PDO;

    private ?PDOFixture $fixture = null;

    public function getFixture(): PDOFixture
    {
        if (!$this->fixture) {
            $this->fixture = $this->createFixture();
        }
        return $this->fixture;
    }

    abstract public function createFixture(): PDOFixture;
}
