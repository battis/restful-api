<?php

namespace Battis\PHPUnit\PDO\Constraints\Assertions;

use Battis\PHPUnit\PDO\Constraints\TableContainsRow;
use Battis\PHPUnit\PDO\Fixture\Row;
use Battis\PHPUnit\PDO\Fixture\Table;
use PDO;
use PHPUnit\Framework\Assert;

trait AssertTableContainsRow
{
    public static function assertTableContainsRow(
        Row $needle,
        Table $haystack,
        PDO $context
    ): void {
        Assert::assertThat($needle, new TableContainsRow($haystack, $context));
    }

    public static function assertTableDoesNotContainRow(
        Row $needle,
        Table $haystack,
        PDO $context
    ): void {
        Assert::assertThat(
            $needle,
            Assert::logicalNot(new TableContainsRow($haystack, $context))
        );
    }
}
