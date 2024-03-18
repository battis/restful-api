<?php

namespace Battis\PHPUnit\PDO\Tests\Fixture;

use Battis\PHPUnit\PDO\Fixture\Column;
use PHPUnit\Framework\TestCase;
use stdClass;

class ColumnTest extends TestCase
{
    /** @var string[] */
    private ?array $names = null;

    /** @var mixed[] */
    private ?array $values = null;

    protected function setUp(): void
    {
        $this->names = null;
        $this->values = null;
    }

    private function getNames(): array
    {
        if (!$this->names) {
            $this->names = [
                'a',
                'A',
                'lower',
                'Title',
                'ALLCAPS',
                'mUlTiCaSe',
                'multiple words',
                'Titled multiple words',
                'snake_case',
                'Cameled_snake_Case',
                "special characters: 123, and @#%'`\\!",
            ];
        }
        return $this->names;
    }

    private function getValues(): array
    {
        if (!$this->values) {
            $this->values = [
                -1,
                0,
                1,
                -3.14159,
                0.0,
                1.6178,
                true,
                false,
                'string',
                'a',
                null,
                new stdClass(),
                [],
            ];
        }
        return $this->values;
    }

    public function testColumn(): void
    {
        foreach ($this->getNames() as $name) {
            $nullColumn = new Column($name);
            $this->assertSame($name, $nullColumn->getName());
            $this->assertNull($nullColumn->getValue());
            foreach ($this->getValues() as $value) {
                $column = new Column($name, $value);
                $this->assertSame($name, $column->getName());
                $this->assertSame($value, $column->getValue());
            }
        }
    }
}
