<?php

namespace Tests\Battis\CRUD;

use Battis\CRUD\Connection;
use Battis\CRUD\Exceptions\RecordException;
use Tests\Battis\CRUD\AbstractDatabaseTest;
use Tests\Battis\CRUD\fixtures\RecordWithFields;

abstract class RecordWithFieldsSqliteMemoryTest extends AbstractDatabaseTest
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
        [
            "id" => 100,
            "foo" => "argle",
            "bar" => "argle-bargle",
            "baz" => "wobbly",
        ],
    ];

    /** @var string */
    private $message = "";

    protected function getType(): string
    {
        return RecordWithFields::class;
    }

    protected function getTableName(): string
    {
        return "record_with_fieldses";
    }

    protected function setUp(): void
    {
        parent::setUp();
        Connection::setPDO($this->getPDO());
        $this->getPDO()->query(
            "CREATE TABLE `" .
                $this->getTableName() .
                "` (id INTEGER PRIMARY KEY, foo VARCHAR(255), bar VARCHAR(255), baz VARCHAR(255))"
        );
    }

    private function messageText($value)
    {
        if (is_array($value)) {
            return json_encode($value);
        } elseif (is_object($value) || $value === null) {
            return var_export($value, true);
        } else {
            return (string) $value;
        }
    }

    protected function initMessage($message)
    {
        $this->message = $this->messageText($message);
    }

    protected function message($details)
    {
        return $this->message . " --> " . $this->messageText($details);
    }

    protected function tearDown(): void
    {
        $this->getPDO()->query("DROP TABLE `" . $this->getTableName() . "`");
        parent::tearDown();
    }

    public function testCreate()
    {
        foreach ($this->tests as $test) {
            $this->initMessage($test);
            $r = $this->getType()::create($test);
            $this->assertInstanceOf($this->getType(), $r, $this->message($r));
            foreach ($test as $key => $value) {
                $this->assertEquals($value, $r->$key, $this->message($value));
            }
            $this->assertDatabaseRowExists(
                $test["id"],
                $this->message($test["id"])
            );
            $this->assertDatabaseRowMatch($test);

            $alreadyUsedId = $this->tests[0]["id"];
            $this->assertNull(
                $this->getType()::create(["id" => $this->tests[0]["id"]]),
                $this->message($alreadyUsedId)
            );
        }
    }

    public function testRead()
    {
        foreach ($this->tests as $test) {
            $this->initMessage($test);
            $this->insertRow($test);
            $r = $this->getType()::read($test["id"]);
            $this->assertInstanceOf($this->getType(), $r, $this->message($r));
            foreach ($test as $key => $value) {
                $this->assertEquals(
                    $value,
                    $r->$key,
                    $this->message("{$r->$key} ≠ $value")
                );
            }
        }

        $this->assertNull($this->getType()::read(count($this->tests) + 1));
    }

    public function testRetrieve()
    {
        $this->insertRows($this->tests);

        $r = $this->getType()::retrieve(["foo" => "argle"]);
        $this->assertCount(count($this->tests), $r);
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
            $this->initMessage($test);
            $this->insertRow($test);
            $r = $this->getType()::read($test["id"]);
            $this->assertInstanceOf($this->getType(), $r, $this->message($r));
            $r->save(["foo" => "updated"]);
            $this->assertEquals("updated", $r->foo);
        }

        $r = $this->getType()::read($this->tests[0]["id"]);
        $this->assertInstanceOf($this->getType(), $r);
        $this->deleteRow($this->tests[0]["id"]);
        $this->expectException(RecordException::class);
        $r->save(["foo" => "fail"]);
    }

    public function testUpdate()
    {
        foreach ($this->tests as $test) {
            $this->initMessage($test);
            $this->insertRow($test);
            $this->assertInstanceOf(
                $this->getType(),
                $this->getType()::read($test["id"]),
                $this->message
            );
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
