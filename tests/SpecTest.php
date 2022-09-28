<?php

namespace Tests\Battis\CRUD;

use Battis\CRUD\Record;
use Battis\CRUD\Spec;
use PHPUnit\Framework\TestCase;
use Tests\Battis\CRUD\fixtures\RecordWithFields;
use Tests\Battis\CRUD\fixtures\RecordWithSetters;

class SpecTest extends TestCase
{
    public function testConstructor()
    {
        foreach (
            [
                [[Record::class], ["records", "id"]],
                [[Record::class, "foo"], ["foo", "id"]],
                [[Record::class, "foo", "bar"], ["foo", "bar"]],
                [[Record::class, "", "foo"], ["records", "foo"]],
                [[Record::class, "foo", "bar", ["baz" => 123]], ["foo", "bar"]],
                [[Record::class, "", "foo"], ["records", "foo"]],
                [[Record::class, "", ""], ["records", "id"]],
                [[Record::class, "", "", ["foo" => "bar"]], ["records", "id"]],
                [[Record::class, "", "", []], ["records", "id"]],
            ]
            as $test
        ) {
            $s = new Spec(...$test[0]);
            $this->assertEquals($test[1][0], $s->getTableName());
            $this->assertEquals($test[1][1], $s->getPrimaryKey());
        }
    }

    public function testGetSetter()
    {
        $s = new Spec(Record::class);
        $this->assertNull($s->getSetter("foo"));

        $s = new Spec(RecordWithFields::class);
        foreach (["id", "foo", "bar"] as $property) {
            $this->assertNull($s->getSetter($property));
        }

        $s = new Spec(RecordWithSetters::class);
        foreach (
            [
                "id" => null,
                "foo" => "setFoo",
                "bar" => null,
                "foo_bar_baz" => "setFooBarBaz",
                "argleBargle" => "setArgleBargle",
            ]
            as $property => $setter
        ) {
            $this->assertEquals($setter, $s->getSetter($property));
        }
    }
}
