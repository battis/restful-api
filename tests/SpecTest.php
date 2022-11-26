<?php

namespace Battis\CRUD\Tests;

use Battis\CRUD\Spec;
use Battis\CRUD\Tests\Fixtures\SpecTest\RecordFixture;
use Battis\CRUD\Tests\Fixtures\SpecTest\SpecFixture;
use PHPUnit\Framework\TestCase;

class SpecTest extends TestCase
{
    public function testConstructor() {
        $values = [
            [
                [RecordFixture::class],
                'record_fixtures',
                Spec::DEFAULT_PRIMARY_KEY,
            ],
            [
                [RecordFixture::class, 'test'],
                'test',
                Spec::DEFAULT_PRIMARY_KEY
            ],
            [
                [RecordFixture::class, 'test', 'foo'],
                'test',
                'foo'
            ],
            [
                [RecordFixture::class, null, 'foo'],
                'record_fixtures',
                'foo'
            ],
            [
                [RecordFixture::class, null, null],
                'record_fixtures',
                Spec::DEFAULT_PRIMARY_KEY,
            ],
            [
                [RecordFixture::class, '', ''],
                'record_fixtures',
                Spec::DEFAULT_PRIMARY_KEY,
            ],
        ];

        foreach ($values as list($args, $table, $primaryKey)) {
            $spec = new Spec(...$args);
            $this->assertEquals($table, $spec->getTableName());
            $this->assertEquals($primaryKey, $spec->getPrimaryKey());
        }
    }

    public function testConstructorHook()
    {
        $spec = new SpecFixture(RecordFixture::class);
        $this->assertSame([], $spec->options);

        $options = ['abc',123, fn($x) => $x + 1, ['foo', 'bar'], true, null];
        $spec = new SpecFixture(RecordFixture::class, null, '', $options);
        $this->assertSame($options, $spec->options);
    }

    public function testGetSetter()
    {
        $values = [
            [
                ['prop1'], 'setProp1'
            ],
            [
                ['prop1', false], 'setProp1'
            ],
            [
                ['prop2'], null
            ],
            [
                ['prop2', false], 'setProp2'
            ],
            [
                ['prop3'], null
            ],
            [
                ['prop3', false], null
            ]
        ];

        $spec = new Spec(RecordFixture::class);
        foreach($values as list($args, $setter)) {
            $this->assertEquals($setter, $spec->getSetter(...$args));
        }
    }
}
