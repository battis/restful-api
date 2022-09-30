<?php

namespace Test\Battis\CRUD;

use Battis\CRUD\Exceptions\RecordException;
use Tests\Battis\CRUD\AbstractDatabaseTest;
use Tests\Battis\CRUD\fixtures\RecordWithFields;

abstract class RecordWithFieldsTest extends AbstractDatabaseTest
{
    private $tests = [
        [
            "id" => 1,
            "foo" => "argle",
            "bar" => "bargle",
        ],
        [
            "id" => 2,
            "foo" => "argle",
            "bar" => "bargle",
            "baz" => "test",
        ],
    ];

    protected function getType(): string
    {
        return RecordWithFields::class;
    }

    protected function getTableName(): string
    {
        return "record_with_fieldses";
    }

    protected function getSetupSQL(): string
    {
        return "CREATE TABLE `" .
            $this->getTableName() .
            "` (id INTEGER PRIMARY KEY, foo VARCHAR(255), bar VARCHAR(255), baz VARCHAR(255))";
    }

    protected function getTearDownSQL(): string
    {
        return "DROP TABLE `" . $this->getTableName() . "`";
    }

    public function testCreate()
    {
        foreach ($this->tests as $test) {
            $r = $this->getType()::create($test);
            $this->assertInstanceOf($this->getType(), $r);
            foreach ($test as $key => $value) {
                $this->assertEquals($value, $r->$key);
            }
            $this->assertDatabaseRowExists($test["id"]);
            $this->assertDatabaseRowMatch($test);

            $this->assertNull(
                $this->getType()::create(["id" => $this->tests[0]["id"]])
            );
        }
    }

    public function testRead()
    {
        foreach ($this->tests as $test) {
            $this->insertRow($test);
            $r = $this->getType()::read($test["id"]);
            $this->assertInstanceOf($this->getType(), $r);
            foreach ($test as $key => $value) {
                $this->assertEquals($value, $r->$key);
            }
        }

        $this->assertNull($this->getType()::read(count($this->tests) + 1));
    }

    public function testRetrieve()
    {
        $this->insertRows($this->tests);

        $r = $this->getType()::retrieve(["foo" => "argle"]);
        $this->assertCount(2, $r);
        foreach ($r as $elt) {
            $this->assertInstanceOf($this->getType(), $elt);
        }

        $r = $this->getType()::retrieve(["baz" => "test"]);
        $this->assertCount(1, $r);
        foreach ($r as $elt) {
            $this->assertInstanceOf($this->getType(), $elt);
        }

        $r = $this->getType()::retrieve(["foo" => "no such data"]);
        $this->assertEmpty($r);
    }

    public function testSave()
    {
        foreach ($this->tests as $test) {
            $this->insertRow($test);
            $r = $this->getType()::read($test["id"]);
            $this->assertInstanceOf($this->getType(), $r);
            $r->save(["foo" => "updated"]);
            $this->assertEquals("updated", $r->foo);
        }

        $r = $this->getType()::read($this->tests[0]["id"]);
        $this->deleteRow($this->tests[0]["id"]);
        $this->expectException(RecordException::class);
        $r->save(["foo" => "this is going to be bad"]);
    }

    public function testUpdate()
    {
        foreach ($this->tests as $test) {
            $this->insertRow($test);
            $r = $this->getType()::update([
                "id" => $test["id"],
                "foo" => "updated",
            ]);
            $this->assertInstanceOf($this->getType(), $r);
            $this->assertEquals("updated", $r->foo);
        }

        $this->assertNull($this->getType()::update(["foo" => "updated"]));
    }

    public function testDelete()
    {
        foreach ($this->tests as $test) {
            $this->insertRow($test);
            $r = $this->getType()::delete($test["id"]);
            $this->assertInstanceOf($this->getType(), $r);
            $this->assertEquals($test["id"], $r->id);
            $this->assertDatabaseExactRowDoesNotExist($test);
        }

        $this->assertNull($this->getType()::delete(count($this->tests) + 1));
    }
}
