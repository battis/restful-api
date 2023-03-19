<?php

namespace Battis\PHPUnit\PDO\Tests\Fixtures\Traits\FixtureTest;

use Battis\PHPUnit\PDO\Fixture\Fixture;
use Battis\PHPUnit\PDO\Query;
use Battis\PHPUnit\PDO\Traits\Fixture as FixtureTrait;
use PDO;

class FixtureFixture
{
    use FixtureTrait;

    /** @var callable(string): string */
    private $getPathToFixture;

    /**
     * @param callable(string): string $getPathToFixture
     */
    public function __construct(callable $getPathToFixture)
    {
        $this->getPathToFixture = $getPathToFixture;
    }

    public function createFixture(): Fixture
    {
        return Fixture::fromYamlFile(
            ($this->getPathToFixture)('fixture.yaml')
        )->withSchema(
            Query::fromSqlFile(($this->getPathToFixture)('schema.sql'))
        );
    }

    public function fixtureGetPDO(): ?PDO
    {
        return static::$pdo;
    }

    public function fixtureGetFixture(): ?Fixture
    {
        return $this->fixture;
    }
}
