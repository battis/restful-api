<?php

namespace Battis\PHPUnit\PDO\Constraints\Assertions;

use Battis\PHPUnit\PDO\Constraints\TableExists;
use Battis\PHPUnit\PDO\Fixture\Table;
use PDO;
use PHPUnit\Framework\Assert;

trait AssertTableExists
{
    public static function assertTableExists(Table $needle, PDO $haystack): void
    {
        Assert::assertThat($needle, new TableExists($haystack));
    }

    public static function assertTableDoesNotExist(Table $needle, PDO $haystack): void
    {
        Assert::assertThat(
            $needle,
            Assert::logicalNot(new TableExists($haystack))
        );
    }
}
